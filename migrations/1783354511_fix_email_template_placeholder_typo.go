package migrations

import (
	"strings"

	"github.com/pocketbase/pocketbase/core"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(app core.App) error {
		usersCollection, err := app.FindCollectionByNameOrId("users")
		if err != nil {
			return err
		}

		usersCollection.ConfirmEmailChangeTemplate.Body = strings.ReplaceAll(
			usersCollection.ConfirmEmailChangeTemplate.Body,
			"{Record:email}",
			"{RECORD:email}",
		)

		return app.Save(usersCollection)
	}, nil)
}
