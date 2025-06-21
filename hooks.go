package presentator

import (
	"net/http"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/apis"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/hook"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/routine"
	"github.com/pocketbase/pocketbase/tools/security"
	"github.com/pocketbase/pocketbase/tools/types"
	"github.com/presentator/presentator/v3/ui"
	"github.com/spf13/cast"
)

func bindAppHooks(app core.App) {
	registerSystemEmails(app)

	app.Cron().MustAdd("send_unread_notifications", "*/10 * * * *", func() {
		if err := processUnreadNotifications(app, 1000); err != nil {
			app.Logger().Error("Notifications cron failure", "error", err)
		}
	})

	app.OnServe().BindFunc(func(e *core.ServeEvent) error {
		// serves static files from the /ui/dist directory
		e.Router.GET("/{path...}", apis.Static(ui.DistDirFS, false)).
			BindFunc(func(e *core.RequestEvent) error {
				// add default Cache-Control header for all ui resources // (ignoring the root path)
				if e.Request.URL.Path != "/" {
					e.Response.Header().Set("Cache-Control", "max-age=1209600, stale-while-revalidate=86400")
				}
				return e.Next()
			}).
			Bind(apis.Gzip())

		// custom routes
		// -----------------------------------------------------------
		e.Router.GET("/api/pr/options", func(e *core.RequestEvent) error {
			sreensCollection, err := e.App.FindCachedCollectionByNameOrId("screens")
			if err != nil {
				return e.BadRequestError("Failed to retrieve cached screens collection", err)
			}
			var maxScreenFileSize int64
			fileField, ok := sreensCollection.Fields.GetByName("file").(*core.FileField)
			if ok {
				maxScreenFileSize = fileField.MaxSize
			}

			links := cast.ToStringMapString(app.Store().Get(OptionFooterLinks))
			if links == nil {
				links = map[string]string{}
			}

			options := struct {
				Links             map[string]string `json:"links"`
				AppName           string            `json:"appName"`
				AppURL            string            `json:"appURL"`
				TermsURL          string            `json:"termsURL"`
				AllowHotspotsURL  bool              `json:"allowHotspotsURL"`
				MaxScreenFileSize int64             `json:"maxScreenFileSize"`
			}{
				Links:             links,
				AppName:           app.Settings().Meta.AppName,
				AppURL:            app.Settings().Meta.AppURL,
				TermsURL:          cast.ToString(app.Store().Get(OptionTermsURL)),
				AllowHotspotsURL:  cast.ToBool(app.Store().Get(OptionAllowHotspotsURL)),
				MaxScreenFileSize: maxScreenFileSize,
			}

			return e.JSON(http.StatusOK, options)
		})

		e.Router.POST(
			"/api/pr/duplicate-prototype/{prototypeId}",
			duplicatePrototype,
		).Bind(apis.RequireAuth(core.CollectionNameSuperusers, "users"))

		e.Router.POST("/api/pr/report", func(e *core.RequestEvent) error {
			link := e.Auth
			if link == nil {
				return e.NotFoundError("Missing auth link context.", nil)
			}

			form := newReportForm(e.App, link)

			if err := e.BindBody(form); err != nil {
				return e.BadRequestError("Failed to read the request data.", err)
			}

			if err := form.Submit(); err != nil {
				return e.BadRequestError("Failed to submit the report.", err)
			}

			return e.NoContent(http.StatusNoContent)
		}).Bind(apis.RequireAuth("links"))

		e.Router.POST("/api/pr/share/{linkId}", func(e *core.RequestEvent) error {
			link, err := e.App.FindRecordById("links", e.Request.PathValue("linkId"))
			if err != nil {
				return e.NotFoundError("Missing or invalid project link.", err)
			}

			project, err := e.App.FindRecordById("projects", link.GetString("project"))
			if err != nil {
				return e.NotFoundError("Failed to load link project.", err)
			}

			if !e.HasSuperuserAuth() {
				info, err := e.RequestInfo()
				if err != nil {
					return e.InternalServerError("", err)
				}
				if ok, err := e.App.CanAccessRecord(project, info, project.Collection().ViewRule); !ok {
					return e.ForbiddenError("You are not allowed to share the project link.", err)
				}
			}

			form := newShareForm(e.App, project, link, e.Auth)

			if err := e.BindBody(form); err != nil {
				return e.BadRequestError("Failed to read the request data.", err)
			}

			if err := form.Submit(); err != nil {
				return e.BadRequestError("An error occured during the submission.", err)
			}

			return e.NoContent(http.StatusNoContent)
		}).Bind(apis.RequireAuth(core.CollectionNameSuperusers, "users"))

		return e.Next()
	})

	// authenticate directly without password for unprotected links
	// ---------------------------------------------------------------
	app.OnRecordAuthWithPasswordRequest("links").Bind(&hook.Handler[*core.RecordAuthWithPasswordRequestEvent]{
		Func: func(e *core.RecordAuthWithPasswordRequestEvent) error {
			if e.Record == nil || e.Record.GetBool("passwordProtect") {
				return e.Next()
			}

			return apis.RecordAuthResponse(e.RequestEvent, e.Record, "", nil)
		},
		Priority: 9999, // execute as later as possible
	})

	// @todo consider removing since v0.23.0 has autogenerate pattern support
	// overwrite user submitted username with random generated "slug"
	// ---------------------------------------------------------------
	app.OnRecordCreateRequest("links").BindFunc(func(e *core.RecordRequestEvent) error {
		e.Record.Set("username", security.RandomStringWithAlphabet(9, "abcdefghijklmnopqrstuvwxyz0123456789"))

		return e.Next()
	})

	// validate hotspot settings
	// ---------------------------------------------------------------
	onHotspotsCreateOrUpdate := func(e *core.RecordRequestEvent) error {
		if err := filterAndValidateHotspotSettings(e.App, e.Record); err != nil {
			return err
		}

		return e.Next()
	}
	app.OnRecordCreateRequest("hotspots").BindFunc(onHotspotsCreateOrUpdate)
	app.OnRecordUpdateRequest("hotspots").BindFunc(onHotspotsCreateOrUpdate)

	// sync project owners list with the preferences collection
	// ---------------------------------------------------------------
	app.OnRecordCreateRequest("projects").BindFunc(func(e *core.RecordRequestEvent) error {
		if err := e.Next(); err != nil {
			return err
		}

		authRecord := e.Auth

		project := e.Record
		userIds := project.GetStringSlice("users")

		createDefaultPreferences(e.App, project, userIds)

		// send notifications to all other owners
		routine.FireAndForget(func() {
			for _, id := range userIds {
				if authRecord == nil || authRecord.Id == id {
					continue // admin or the current auth user
				}

				if err := sendAddedOwnerEmail(app, project, authRecord, id); err != nil {
					app.Logger().Warn("Failed to notify assigned project owner", "project", project.Id, "error", err)
				}
			}
		})

		return nil
	})

	app.OnRecordUpdateRequest("projects").BindFunc(func(e *core.RecordRequestEvent) error {
		if err := e.Next(); err != nil {
			return err
		}

		authRecord := e.Auth

		project := e.Record

		oldUserIds := project.Original().GetStringSlice("users")
		newUserIds := project.GetStringSlice("users")
		newIds := list.SubtractSlice(newUserIds, oldUserIds)
		removedIds := list.SubtractSlice(oldUserIds, newUserIds)

		if len(newIds) > 0 {
			// create new users prefs
			createDefaultPreferences(e.App, project, newIds)

			// notify assigned
			routine.FireAndForget(func() {
				for _, id := range newIds {
					if err := sendAddedOwnerEmail(app, project, authRecord, id); err != nil {
						app.Logger().Warn("Failed to notify assigned project owner", "project", project.Id, "error", err)
					}
				}
			})
		}

		if len(removedIds) > 0 {
			// delete removed user prefs
			for _, id := range removedIds {
				pref, err := e.App.FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": project.Id,
					"user":    id,
				})
				if err != nil {
					e.App.Logger().Warn("Missing user project preference to delete", "error", err, "user", id, "project", project.Id)
				} else if err := e.App.Delete(pref); err != nil {
					e.App.Logger().Error("Failed to delete user project preference", "error", err, "user", id, "project", project.Id)
				}
			}

			// notify unassigned
			routine.FireAndForget(func() {
				for _, id := range removedIds {
					if err := sendRemovedOwnerEmail(app, project, authRecord, id); err != nil {
						app.Logger().Warn("Failed to notify unassigned project owner", "project", project.Id, "error", err)
					}
				}
			})
		}

		return nil
	})

	// update the lastVisited project preference for the current user
	// on project view and when uploading/replacing screens
	// ---------------------------------------------------------------
	app.OnRecordViewRequest("projects").BindFunc(func(e *core.RecordRequestEvent) error {
		if e.Auth == nil || e.Auth.Collection().Name != "users" {
			return e.Next()
		}

		if err := updateLastVisitedPreference(e.App, e.Auth.Id, e.Record.Id); err != nil {
			e.App.Logger().Warn(
				"Failed to update lastVisited project preference",
				"error", err,
				"project", e.Record.Id,
				"user", e.Auth.Id,
			)
		}

		return e.Next()
	})

	onScreenCreateOrUpdate := func(e *core.RecordRequestEvent) error {
		if err := e.Next(); err != nil {
			return err
		}

		if e.Auth == nil || e.Auth.Collection().Name != "users" {
			return nil
		}

		screen := e.Record

		prototype, err := e.App.FindRecordById("prototypes", screen.GetString("prototype"))
		if err != nil {
			e.App.Logger().Warn("Failed to find screen prototype to update its project lastVisited preference and screensOrder", "error", err, "screen", screen.Id, "prototype", screen.GetString("prototype"))
			return nil
		}

		// update lastVisited
		// ---
		projectId := prototype.GetString("project")
		if err := updateLastVisitedPreference(e.App, e.Auth.Id, projectId); err != nil {
			e.App.Logger().Warn(
				"Failed to update lastVisited project preference",
				"error", err,
				"project", projectId,
				"user", e.Auth.Id,
				"screen", screen.Id,
			)
		}

		// if not already, remove the screen from the old prototype screensOrder field
		// ---
		oldPrototypeId := screen.Original().GetString("prototype")
		if oldPrototypeId == "" || oldPrototypeId == prototype.Id {
			return nil // no prototype change
		}

		oldPrototype, err := e.App.FindRecordById("prototypes", oldPrototypeId)
		if err != nil {
			e.App.Logger().Warn("Failed to find old screen prototype to update its screensOrder", "error", err, "screen", screen.Id, "prototype", oldPrototypeId)
			return nil
		}

		oldScreenIds := oldPrototype.GetStringSlice("screensOrder")
		if list.ExistInSlice(screen.Id, oldScreenIds) {
			oldPrototype.Set("screensOrder", list.SubtractSlice(oldScreenIds, []string{screen.Id}))
			if err := e.App.Save(oldPrototype); err != nil {
				e.App.Logger().Warn(
					"Failed to update old prototype's screensOrder",
					"error", err,
					"prototype", prototype.Id,
					"screen", screen.Id,
					"user", e.Auth.Id,
				)
			}
		}

		return nil
	}
	app.OnRecordCreateRequest("screens").BindFunc(onScreenCreateOrUpdate)
	app.OnRecordUpdateRequest("screens").BindFunc(onScreenCreateOrUpdate)

	// create collaborators notifications after each new comment
	// ---------------------------------------------------------------
	app.OnRecordCreateRequest("comments").BindFunc(func(e *core.RecordRequestEvent) error {
		if err := e.Next(); err != nil {
			return err
		}

		if err := createNotifications(e.App, e.Record); err != nil {
			e.App.Logger().Error("Failed to create comment notification", "comment", e.Record.Id, "error", err)
		}

		if err := sendGuestsEmail(e.App, e.Record); err != nil {
			e.App.Logger().Error("Failed to send emails to all guests", "comment", e.Record.Id, "error", err)
		}

		return nil
	})
}

func createDefaultPreferences(app core.App, project *core.Record, userIds []string) {
	if len(userIds) == 0 {
		return
	}

	preferencesCollection, err := app.FindCollectionByNameOrId("projectUserPreferences")
	if err != nil {
		app.Logger().Error("Failed to find preferences collection", "error", err)
		return
	}

	for _, id := range userIds {
		pref := core.NewRecord(preferencesCollection)
		pref.Set("project", project.Id)
		pref.Set("user", id)
		pref.Set("watch", true)
		pref.Set("lastVisited", types.NowDateTime())
		if err := app.Save(pref); err != nil {
			app.Logger().Error("Failed to save default user project preferences", "error", err, "user", id, "project", project.Id)
		}
	}
}

func updateLastVisitedPreference(app core.App, userId string, projectId string) error {
	pref, err := app.FindFirstRecordByFilter("projectUserPreferences", "project = {:project} && user = {:user}", dbx.Params{
		"project": projectId,
		"user":    userId,
	})
	if err != nil {
		return err
	}

	pref.Set("lastVisited", types.NowDateTime())

	return app.Save(pref)
}
