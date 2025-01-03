package presentator

import (
	"fmt"
	"net/http"
	"time"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/apis"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/types"
	"github.com/spf13/cast"
)

func duplicatePrototype(e *core.RequestEvent) error {
	// fetch prototype
	prototype, err := e.App.FindRecordById("prototypes", e.Request.PathValue("prototypeId"))
	if err != nil {
		return e.NotFoundError("Missing or invalid prototype.", err)
	}

	// check whether the current user is allowed to create a prototype in the project
	info, err := e.RequestInfo()
	if err != nil {
		return e.InternalServerError("", err)
	}
	if !info.HasSuperuserAuth() {
		if ok, err := e.App.CanAccessRecord(prototype, info, prototype.Collection().CreateRule); !ok {
			return e.ForbiddenError("You are not allowed to access or duplicate the prototype.", err)
		}
	}

	// fetch screens
	screens, err := e.App.FindAllRecords("screens", dbx.HashExp{"prototype": prototype.Id})
	if err != nil {
		return e.BadRequestError("Failed to duplicate prototype screens.", err)
	}

	// fetch hotspot templates
	templates, err := e.App.FindAllRecords("hotspotTemplates", dbx.HashExp{"prototype": prototype.Id})
	if err != nil {
		return e.BadRequestError("Failed to fetch prototype hotspot templates.", err)
	}

	// extract screen ids
	oldScreenIds := make([]any, len(screens))
	for i, s := range screens {
		oldScreenIds[i] = s.Id
	}

	// extract template ids
	oldTemplateIds := make([]any, len(templates))
	for i, t := range templates {
		oldTemplateIds[i] = t.Id
	}

	// fetch hotspots
	hotspots, err := e.App.FindAllRecords("hotspots", dbx.Or(
		dbx.In("screen", oldScreenIds...),
		dbx.In("hotspotTemplate", oldTemplateIds...),
	))
	if err != nil {
		return e.BadRequestError("Failed to fetch prototype hotspots.", err)
	}

	// initialize the file system to copy later the screen files
	fsys, err := e.App.NewFilesystem()
	if err != nil {
		return e.InternalServerError("Files storage error.", err)
	}
	defer fsys.Close()

	// -----------------------------------------------------------
	// Start the duplication
	// -----------------------------------------------------------

	nowDateTime := types.NowDateTime()

	timestamp := cast.ToString(time.Now().Unix())

	// map with old->new screen file paths to duplicate
	var files = make(map[string]string, len(screens))

	var prototypeClone *core.Record

	txErr := e.App.RunInTransaction(func(txApp core.App) error {
		// duplicate prototypes
		// ---
		prototypeClone = prototype.Original()
		prototypeClone.MarkAsNew()
		prototypeClone.Set("id:autogenerate", "")
		prototypeClone.SetRaw("created", nowDateTime)
		prototypeClone.SetRaw("updated", nowDateTime)
		prototypeClone.Set("title", prototype.GetString("title")+" (copy)")
		prototypeClone.Set("screensOrder", nil) // will be updated later
		if err := txApp.Save(prototypeClone); err != nil {
			// try again with timestamp suffixed title
			prototypeClone.Set("title", fmt.Sprintf("%s (%s)", prototype.GetString("title"), timestamp))
			if err := txApp.Save(prototypeClone); err != nil {
				return e.BadRequestError("Failed to duplicate prototype.", err)
			}
		}

		// duplicate screens
		// ---
		duplicatedScreenIds := make(map[string]string, len(screens)) // old->new id pairs
		for _, s := range screens {
			clone := s.Original()
			clone.MarkAsNew()
			clone.Set("id:autogenerate", "")
			clone.SetRaw("created", nowDateTime)
			clone.SetRaw("updated", nowDateTime)
			clone.Set("prototype", prototypeClone.Id)
			clone.Set("file", timestamp+"_"+clone.GetString("file"))

			if err := txApp.SaveNoValidate(clone); err != nil {
				return e.BadRequestError("Failed to duplicate screen "+s.GetString("title")+".", err)
			}

			duplicatedScreenIds[s.Id] = clone.Id

			// store the old->new file references to duplicate later
			files[s.BaseFilesPath()+"/"+s.GetString("file")] = clone.BaseFilesPath() + "/" + clone.GetString("file")
		}

		// update prototypes screens order
		// ---
		oldScreensOrder := prototype.GetStringSlice("screensOrder")
		newScreensOrder := make([]string, 0, len(oldScreensOrder))
		for _, oldId := range oldScreensOrder {
			if newId, ok := duplicatedScreenIds[oldId]; ok {
				newScreensOrder = append(newScreensOrder, newId)
			}
		}
		prototypeClone.Set("screensOrder", newScreensOrder)
		if err := txApp.Save(prototypeClone); err != nil {
			// non-critical error
			txApp.Logger().Warn("Failed to update prototypes screens order", "error", err)
		}

		// duplicate hotspot templates
		// ---
		duplicatedTemplateIds := make(map[string]string, len(templates)) // old->new id pairs
		for _, t := range templates {
			clone := t.Original()
			clone.MarkAsNew()
			clone.Set("id:autogenerate", "")
			clone.SetRaw("created", nowDateTime)
			clone.SetRaw("updated", nowDateTime)
			clone.Set("prototype", prototypeClone.Id)

			oldScreens := t.GetStringSlice("screens")
			newScreens := make([]string, 0, len(oldScreens))
			for _, oldId := range oldScreens {
				if newId, ok := duplicatedScreenIds[oldId]; ok {
					newScreens = append(newScreens, newId)
				}
			}
			clone.Set("screens", newScreens)

			if err := txApp.Save(clone); err != nil {
				return e.BadRequestError("Failed to duplicate hotspot template.", err)
			}

			duplicatedTemplateIds[t.Id] = clone.Id
		}

		// duplicate hotspots
		// ---
		for _, h := range hotspots {
			clone := h.Original()
			clone.MarkAsNew()
			clone.Set("id:autogenerate", "")
			clone.SetRaw("created", nowDateTime)
			clone.SetRaw("updated", nowDateTime)

			oldScreen := h.GetString("screen")
			if oldScreen != "" && duplicatedScreenIds[oldScreen] != "" {
				clone.Set("screen", duplicatedScreenIds[oldScreen])
			}

			oldTemplate := h.GetString("hotspotTemplate")
			if oldTemplate != "" && duplicatedTemplateIds[oldTemplate] != "" {
				clone.Set("hotspotTemplate", duplicatedTemplateIds[oldTemplate])
			}

			settings := map[string]any{}
			if err := clone.UnmarshalJSONField("settings", &settings); err != nil {
				return e.BadRequestError(
					"Failed to duplicate hotspot.",
					fmt.Errorf("failed to unmarshal hotspot settings: %w", err),
				)
			}
			settingsScreen := cast.ToString(settings["screen"])
			if settingsScreen != "" && duplicatedScreenIds[settingsScreen] != "" {
				settings["screen"] = duplicatedScreenIds[settingsScreen]
				clone.Set("settings", settings)
			}

			if err := txApp.Save(clone); err != nil {
				return e.BadRequestError("Failed to duplicate hotspot.", err)
			}
		}

		return nil
	})
	if txErr != nil {
		return txErr
	}

	// copy screen files
	//
	// note: we don't execute the files copy in the transaction
	// to avoid blocking other writes for too long during the upload
	for oldKey, newKey := range files {
		if copyErr := fsys.Copy(oldKey, newKey); copyErr != nil {
			// attemp to cleanup the previously created prototype (its relations will be deleted via cascade)
			if err := e.App.Delete(prototypeClone); err != nil {
				e.App.Logger().Warn(
					"Failed to cleanup cloned prototype after file copy error.",
					"error", err,
					"copyError", copyErr,
					"oldFile", oldKey,
					"newFile", newKey,
				)
			}

			return e.BadRequestError("Failed to copy screen file "+oldKey+".", copyErr)
		}
	}

	if err := apis.EnrichRecord(e, prototypeClone); err != nil {
		e.App.Logger().Debug("Unable to enrich the duplicated prototype", "error", err)
	}

	return e.JSON(http.StatusOK, prototypeClone)
}
