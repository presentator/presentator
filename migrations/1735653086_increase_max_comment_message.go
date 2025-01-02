package migrations

import (
	"errors"

	"github.com/pocketbase/pocketbase/core"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(app core.App) error {
		commentsCollection, err := app.FindCollectionByNameOrId("comments")
		if err != nil {
			return err
		}

		field, ok := commentsCollection.Fields.GetByName("message").(*core.TextField)
		if !ok {
			return errors.New("missing message TextField")
		}
		field.Max = 1000

		return app.Save(commentsCollection)
	}, nil)
}
