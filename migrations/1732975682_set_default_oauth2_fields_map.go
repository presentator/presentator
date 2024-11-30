package migrations

import (
	"github.com/pocketbase/pocketbase/core"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(app core.App) error {
		usersCollection, err := app.FindCollectionByNameOrId("users")
		if err != nil {
			return err
		}

		usersCollection.OAuth2.MappedFields.Username = "username"
		usersCollection.OAuth2.MappedFields.AvatarURL = "avatar"
		usersCollection.OAuth2.MappedFields.Name = "name"

		return app.Save(usersCollection)
	}, nil)
}
