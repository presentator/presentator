package migrations

import (
	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/daos"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(db dbx.Builder) error {
		dao := daos.New(db)

		settings, _ := dao.FindSettings()
		settings.Meta.AppName = "Presentator"
		settings.Meta.HideControls = true
		settings.Meta.VerificationTemplate.Hidden = true
		settings.Meta.ResetPasswordTemplate.Hidden = true
		settings.Meta.ConfirmEmailChangeTemplate.Hidden = true

		return dao.SaveSettings(settings)
	}, nil)
}
