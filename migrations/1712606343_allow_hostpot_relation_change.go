package migrations

import (
	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/daos"
	m "github.com/pocketbase/pocketbase/migrations"
)

// Updates the hotspots Update API rule to allow changing a hotspot
// relation from screen to hotspotTemplate (and the opposite).
func init() {
	m.Register(func(db dbx.Builder) error {
		dao := daos.New(db)

		collection, err := dao.FindCollectionByNameOrId("hotspots")
		if err != nil {
			return err
		}

		rule := ("@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n) &&\n// screen change\n(\n  @request.data.screen:isset = false ||\n  @request.data.screen = screen ||\n  @request.data.screen.prototype.project.users.id ?= @request.auth.id ||\n  (@request.data.screen = \"\" && @request.data.hotspotTemplate != \"\")\n) &&\n// hotspotTemplate change\n(\n  @request.data.hotspotTemplate:isset = false ||\n  @request.data.hotspotTemplate = hotspotTemplate ||\n  @request.data.hotspotTemplate.prototype.project.users.id ?= @request.auth.id ||\n  (@request.data.hotspotTemplate = \"\" && @request.data.screen != \"\")\n)")

		collection.UpdateRule = &rule

		return dao.Save(collection)
	}, func(db dbx.Builder) error {
		dao := daos.New(db)

		collection, err := dao.FindCollectionByNameOrId("hotspots")
		if err != nil {
			return err
		}

		rule := ("@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n) &&\n// screen change\n(\n  @request.data.screen:isset = false ||\n@request.data.screen = screen ||\n  @request.data.screen.prototype.project.users.id ?= @request.auth.id\n) &&\n// hotspotTemplate change\n(\n  @request.data.hotspotTemplate:isset = false ||\n  @request.data.hotspotTemplate = hotspotTemplate ||\n  @request.data.hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n)")

		collection.UpdateRule = &rule

		return dao.Save(collection)
	})
}
