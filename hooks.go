package presentator

import (
	"context"
	"fmt"
	"net/http"
	"time"

	"github.com/labstack/echo/v5"
	"github.com/labstack/echo/v5/middleware"
	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/apis"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/forms"
	"github.com/pocketbase/pocketbase/models"
	"github.com/pocketbase/pocketbase/tools/auth"
	"github.com/pocketbase/pocketbase/tools/cron"
	"github.com/pocketbase/pocketbase/tools/filesystem"
	"github.com/pocketbase/pocketbase/tools/hook"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/routine"
	"github.com/pocketbase/pocketbase/tools/security"
	"github.com/pocketbase/pocketbase/tools/types"
	"github.com/presentator/presentator/v3/ui"
	"github.com/spf13/cast"
)

func uiCacheControl() echo.MiddlewareFunc {
	return func(next echo.HandlerFunc) echo.HandlerFunc {
		return func(c echo.Context) error {
			// add default Cache-Control header for all ui resources
			// (ignoring the root path)
			if c.Request().URL.Path != "/" {
				c.Response().Header().Set("Cache-Control", "max-age=1209600, stale-while-revalidate=86400")
			}

			return next(c)
		}
	}
}

func bindAppHooks(app core.App) {
	registerSystemEmails(app)

	app.OnBeforeServe().Add(func(e *core.ServeEvent) error {
		e.Router.Use(middleware.BodyLimit(300000000)) // max ~300MB body

		// serves static files from the /ui/dist directory
		e.Router.GET(
			"/*",
			apis.StaticDirectoryHandler(ui.DistDirFS, false),
			uiCacheControl(),
			middleware.Gzip(),
		)

		// init default cron
		// -----------------------------------------------------------
		scheduler := cron.New()
		scheduler.MustAdd("unread", "*/10 * * * *", func() {
			if err := processUnreadNotifications(app, 1000); err != nil {
				app.Logger().Error("Notifications cron failure", "error", err)
			}
		})
		scheduler.Start()

		// custom routes
		// -----------------------------------------------------------
		e.Router.GET("/api/pr/options", func(c echo.Context) error {
			options := struct {
				Links            map[string]string `json:"links"`
				AppName          string            `json:"appName"`
				AppUrl           string            `json:"appUrl"`
				TermsUrl         string            `json:"termsUrl"`
				AllowHotspotsUrl bool              `json:"allowHotspotsUrl"`
			}{
				Links:            cast.ToStringMapString(app.Store().Get(OptionFooterLinks)),
				AppName:          app.Settings().Meta.AppName,
				AppUrl:           app.Settings().Meta.AppUrl,
				TermsUrl:         cast.ToString(app.Store().Get(OptionTermsUrl)),
				AllowHotspotsUrl: cast.ToBool(app.Store().Get(OptionAllowHotspotsUrl)),
			}

			return c.JSON(http.StatusOK, options)
		}, apis.ActivityLogger(app))

		e.Router.POST(
			"/api/pr/duplicate-prototype/:prototypeId",
			duplicatePrototype(app),
			apis.ActivityLogger(app),
			apis.RequireAdminOrRecordAuth("users"),
		)

		e.Router.POST("/api/pr/report", func(c echo.Context) error {
			link, _ := c.Get(apis.ContextAuthRecordKey).(*models.Record)
			if link == nil {
				return apis.NewNotFoundError("Missing auth link context.", nil)
			}

			form := newReportForm(app, link)

			if err := c.Bind(form); err != nil {
				return apis.NewBadRequestError("Failed to read the request data.", err)
			}

			if err := form.Submit(); err != nil {
				return apis.NewBadRequestError("Failed to submit the report.", err)
			}

			return c.NoContent(http.StatusNoContent)
		}, apis.ActivityLogger(app), apis.RequireRecordAuth("links"))

		e.Router.POST("/api/pr/share/:linkId", func(c echo.Context) error {
			link, err := app.Dao().FindRecordById("links", c.PathParam("linkId"))
			if err != nil {
				return apis.NewNotFoundError("Missing or invalid project link.", err)
			}

			project, err := app.Dao().FindRecordById("projects", link.GetString("project"))
			if err != nil {
				return apis.NewNotFoundError("Failed to load link project.", err)
			}

			info := apis.RequestInfo(c)
			if info.Admin == nil {
				if ok, err := app.Dao().CanAccessRecord(project, info, project.Collection().ViewRule); !ok {
					return apis.NewForbiddenError("You are not allowed to share the project link.", err)
				}
			}

			form := newShareForm(app, project, link)

			if err := c.Bind(form); err != nil {
				return apis.NewBadRequestError("Failed to read the request data.", err)
			}

			if err := form.Submit(); err != nil {
				return apis.NewBadRequestError("An error occured during the submission.", err)
			}

			return c.NoContent(http.StatusNoContent)
		}, apis.ActivityLogger(app), apis.RequireAdminOrRecordAuth("users"))

		return nil
	})

	// use the OAuth2 profile data to populate the newly created user
	// ---------------------------------------------------------------
	app.OnRecordAfterAuthWithOAuth2Request("users").Add(func(e *core.RecordAuthWithOAuth2Event) error {
		if !e.IsNewRecord {
			return nil
		}

		if err := updateAuthRecordWithOAuth2User(app, e.Record, e.OAuth2User); err != nil {
			app.Logger().Warn("Failed to update user with the OAuth2 profile data", "error", err)
		}

		return nil
	})

	// authenticate directly without password for unprotected links
	// ---------------------------------------------------------------
	app.OnRecordBeforeAuthWithPasswordRequest("links").Add(func(e *core.RecordAuthWithPasswordEvent) error {
		if e.Record == nil || e.Record.GetBool("passwordProtect") {
			return nil
		}

		if err := apis.RecordAuthResponse(app, e.HttpContext, e.Record, nil); err != nil {
			return err
		}

		return hook.StopPropagation
	})

	// generate random 8 character link "slug"
	// ---------------------------------------------------------------
	app.OnRecordBeforeCreateRequest("links").Add(func(e *core.RecordCreateEvent) error {
		return e.Record.SetUsername(security.RandomStringWithAlphabet(8, "abcdefghijklmnopqrstuvwxyz0123456789"))
	})

	// validate hotspot settings
	// ---------------------------------------------------------------
	app.OnRecordBeforeCreateRequest("hotspots").Add(func(e *core.RecordCreateEvent) error {
		return filterAndValidateHotspotSettings(app, e.Record)
	})

	app.OnRecordBeforeUpdateRequest("hotspots").Add(func(e *core.RecordUpdateEvent) error {
		return filterAndValidateHotspotSettings(app, e.Record)
	})

	// sync project owners list with the preferences collection
	// ---------------------------------------------------------------
	app.OnRecordAfterCreateRequest("projects").Add(func(e *core.RecordCreateEvent) error {
		userIds := e.Record.GetStringSlice("users")
		authRecord, _ := e.HttpContext.Get(apis.ContextAuthRecordKey).(*models.Record)

		createDefaultPreferences(app, e.Record, userIds)

		// send notifications to all other owners
		routine.FireAndForget(func() {
			for _, id := range userIds {
				if authRecord == nil || authRecord.Id == id {
					continue // admin or the current auth user
				}

				if err := sendAddedOwnerEmail(app, e.Record, id); err != nil {
					app.Logger().Warn("Failed to notify assigned project owner", "project", e.Record.Id, "error", err)
				}
			}
		})

		return nil
	})

	app.OnRecordAfterUpdateRequest("projects").Add(func(e *core.RecordUpdateEvent) error {
		oldUserIds := e.Record.OriginalCopy().GetStringSlice("users")
		newUserIds := e.Record.GetStringSlice("users")
		newIds := list.SubtractSlice(newUserIds, oldUserIds)
		removedIds := list.SubtractSlice(oldUserIds, newUserIds)

		if len(newIds) > 0 {
			// create new users prefs
			createDefaultPreferences(app, e.Record, newIds)

			// notify assigned
			routine.FireAndForget(func() {
				for _, id := range newIds {
					if err := sendAddedOwnerEmail(app, e.Record, id); err != nil {
						app.Logger().Warn("Failed to notify assigned project owner", "project", e.Record.Id, "error", err)
					}
				}
			})
		}

		if len(removedIds) > 0 {
			// delete removed user prefs
			for _, id := range removedIds {
				pref, err := app.Dao().FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": e.Record.Id,
					"user":    id,
				})
				if err != nil {
					app.Logger().Warn("Missing user project preference to delete", "error", err, "user", id, "project", e.Record.Id)
				} else if err := app.Dao().DeleteRecord(pref); err != nil {
					app.Logger().Error("Failed to delete user project preference", "error", err, "user", id, "project", e.Record.Id)
				}
			}

			// notify unassigned
			routine.FireAndForget(func() {
				for _, id := range removedIds {
					if err := sendRemovedOwnerEmail(app, e.Record, id); err != nil {
						app.Logger().Warn("Failed to notify unassigned project owner", "project", e.Record.Id, "error", err)
					}
				}
			})
		}

		return nil
	})

	// update the lastVisited project preference for the current user
	// on project view and when uploading/replacing screens
	// ---------------------------------------------------------------
	app.OnRecordViewRequest("projects").Add(func(e *core.RecordViewEvent) error {
		authRecord, _ := e.HttpContext.Get(apis.ContextAuthRecordKey).(*models.Record)
		if authRecord == nil || authRecord.Collection().Name != "users" {
			return nil
		}

		if err := updateLastVisitedPreference(app, authRecord.Id, e.Record.Id); err != nil {
			app.Logger().Warn(
				"Failed to update lastVisited project preference",
				"error", err,
				"project", e.Record.Id,
				"user", authRecord.Id,
			)
		}

		return nil
	})

	onScreenCreateOrUpdate := func(c echo.Context, screen *models.Record) error {
		authRecord, _ := c.Get(apis.ContextAuthRecordKey).(*models.Record)
		if authRecord == nil || authRecord.Collection().Name != "users" {
			return nil
		}

		prototype, err := app.Dao().FindRecordById("prototypes", screen.GetString("prototype"))
		if err != nil {
			app.Logger().Warn("Failed to find screen prototype to update its project lastVisited preference and screensOrder", "error", err, "screen", screen.Id, "prototype", screen.GetString("prototype"))
			return nil
		}

		// update lastVisited
		// ---
		projectId := prototype.GetString("project")
		if err := updateLastVisitedPreference(app, authRecord.Id, projectId); err != nil {
			app.Logger().Warn(
				"Failed to update lastVisited project preference",
				"error", err,
				"project", projectId,
				"user", authRecord.Id,
				"screen", screen.Id,
			)
		}

		// if not already, remove the screen from the old prototype screensOrder field
		// ---
		oldPrototypeId := screen.OriginalCopy().GetString("prototype")
		if oldPrototypeId == "" || oldPrototypeId == prototype.Id {
			return nil // no prototype change
		}

		oldPrototype, err := app.Dao().FindRecordById("prototypes", oldPrototypeId)
		if err != nil {
			app.Logger().Warn("Failed to find old screen prototype to update its screensOrder", "error", err, "screen", screen.Id, "prototype", oldPrototypeId)
			return nil
		}

		oldScreenIds := oldPrototype.GetStringSlice("screensOrder")
		if list.ExistInSlice(screen.Id, oldScreenIds) {
			oldPrototype.Set("screensOrder", list.SubtractSlice(oldScreenIds, []string{screen.Id}))
			if err := app.Dao().SaveRecord(oldPrototype); err != nil {
				app.Logger().Warn(
					"Failed to update old prototype's screensOrder",
					"error", err,
					"prototype", prototype.Id,
					"screen", screen.Id,
					"user", authRecord.Id,
				)
			}
		}

		return nil
	}

	app.OnRecordAfterCreateRequest("screens").Add(func(e *core.RecordCreateEvent) error {
		return onScreenCreateOrUpdate(e.HttpContext, e.Record)
	})

	app.OnRecordAfterUpdateRequest("screens").Add(func(e *core.RecordUpdateEvent) error {
		return onScreenCreateOrUpdate(e.HttpContext, e.Record)
	})

	// create collaborators notifications after each new comment
	// ---------------------------------------------------------------
	app.OnRecordAfterCreateRequest("comments").Add(func(e *core.RecordCreateEvent) error {
		if err := createNotifications(app, e.Record); err != nil {
			app.Logger().Error("Failed to create comment notification", "comment", e.Record.Id, "error", err)
		}

		if err := sendGuestsEmail(app, e.Record); err != nil {
			app.Logger().Error("Failed to send emails to all guests", "comment", e.Record.Id, "error", err)
		}

		return nil
	})
}

func createDefaultPreferences(app core.App, project *models.Record, userIds []string) {
	if len(userIds) == 0 {
		return
	}

	preferencesCollection, err := app.Dao().FindCollectionByNameOrId("projectUserPreferences")
	if err != nil {
		app.Logger().Error("Failed to find preferences collection", "error", err)
		return
	}

	for _, id := range userIds {
		pref := models.NewRecord(preferencesCollection)
		pref.Set("project", project.Id)
		pref.Set("user", id)
		pref.Set("watch", true)
		pref.Set("lastVisited", types.NowDateTime())
		if err := app.Dao().SaveRecord(pref); err != nil {
			app.Logger().Error("Failed to save default user project preferences", "error", err, "user", id, "project", project.Id)
		}
	}
}

func updateLastVisitedPreference(app core.App, userId string, projectId string) error {
	pref, err := app.Dao().FindFirstRecordByFilter("projectUserPreferences", `project = {:project} && user = {:user}`, dbx.Params{
		"project": projectId,
		"user":    userId,
	})
	if err != nil {
		return err
	}

	pref.Set("lastVisited", types.NowDateTime())

	return app.Dao().SaveRecord(pref)
}

func updateAuthRecordWithOAuth2User(app core.App, record *models.Record, oauth2User *auth.AuthUser) error {
	var needUpdate bool

	form := forms.NewRecordUpsert(app, record)

	if oauth2User.Name != "" && record.GetString("name") == "" {
		needUpdate = true
		form.LoadData(map[string]any{"name": oauth2User.Name})
	}

	if oauth2User.AvatarUrl != "" && record.GetString("avatar") == "" {
		ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
		defer cancel()

		file, err := filesystem.NewFileFromUrl(ctx, oauth2User.AvatarUrl)
		if err != nil {
			return fmt.Errorf("failed to download OAuth2 avatar: %w", err)

		}

		needUpdate = true
		form.AddFiles("avatar", file)
	}

	if !needUpdate {
		return nil
	}

	return form.Submit()
}
