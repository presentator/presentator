package presentator

import (
	"errors"
	"fmt"
	"regexp"
	"strings"
	"time"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/types"
	"golang.org/x/sync/errgroup"
)

func createNotifications(app core.App, comment *core.Record) error {
	notificationsCollection, err := app.FindCollectionByNameOrId("notifications")
	if err != nil {
		return err
	}

	project, err := getProjectWithUsersByComment(app, comment)
	if err != nil {
		return err
	}

	var errs []error

	users := project.ExpandedAll("users")
	for _, user := range users {
		// skip if the author of the comment
		if comment.GetString("user") == user.Id {
			continue
		}

		pref, err := app.FindFirstRecordByFilter("projectUserPreferences", "user={:user} && project={:project}", dbx.Params{
			"user":    user.Id,
			"project": project.Id,
		})
		if err != nil {
			errs = append(errs, err)
			continue
		}

		// not watched project
		if !pref.GetBool("watch") {
			// check if mentioned
			mentions := extractMentions(comment.GetString("message"))
			if !list.ExistInSlice(user.GetString("username"), mentions) {
				continue
			}
		}

		record := core.NewRecord(notificationsCollection)
		record.Set("user", user.Id)
		record.Set("comment", comment.Id)
		// mark as processed to prevent sending email notification
		record.Set("processed", !user.GetBool("allowEmailNotifications"))

		if err := app.Save(record); err != nil {
			errs = append(errs, err)
			continue
		}
	}

	return errors.Join(errs...)
}

func getProjectWithUsersByComment(app core.App, comment *core.Record) (*core.Record, error) {
	model := comment.Fresh() // create a copy to avoid modifying the original record
	if errs := app.ExpandRecord(model, []string{"screen.prototype.project.users"}, nil); len(errs) > 0 {
		return nil, fmt.Errorf("failed to expand: %v", errs)
	}

	// extract the project from the expanded relations
	rels := []string{"screen", "prototype", "project"}
	for _, rel := range rels {
		model = model.ExpandedOne(rel)
		if model == nil {
			return nil, fmt.Errorf("missing comment relation %s", rel)
		}
	}

	return model, nil
}

var mentionRegex = regexp.MustCompile(`[@+]([\w\.\-\@]+)`)

func extractMentions(message string) []string {
	matches := mentionRegex.FindAllStringSubmatch(message, -1)

	result := make([]string, 0, len(matches))

	for _, match := range matches {
		if len(match) != 2 {
			continue
		}

		if m := strings.TrimSpace(match[1]); m != "" {
			result = append(result, m)
		}
	}

	return result
}

// -------------------------------------------------------------------

func processUnreadNotifications(app core.App, limit int) error {
	notifications, err := findUnreadNotifications(app, limit)
	if err != nil {
		return fmt.Errorf("failed to fetch unread notifications: %w", err)
	}

	g := new(errgroup.Group)
	g.SetLimit(100)

	for userId, notifications := range notifications {
		userId := userId
		notifications := notifications
		g.Go(func() error {
			if err := sendUnreadEmail(app, notifications, userId); err != nil {
				return err
			}

			var errs []error
			for _, n := range notifications {
				n.Set("processed", true)
				if err := app.Save(n); err != nil {
					errs = append(errs, err)
				}
			}

			return errors.Join(errs...)
		})
	}

	if err := g.Wait(); err != nil {
		return fmt.Errorf("failed to send all unread notifications: %w", err)
	}

	return nil
}

func findUnreadNotifications(app core.App, limit int) (map[string][]*core.Record, error) {
	notifications := []*core.Record{}

	// the notification must be created at least 2 minutes ago
	minDate := time.Now().Add(-2 * time.Minute).UTC().Format(types.DefaultDateLayout)

	query := app.RecordQuery("notifications").
		AndWhere(dbx.NewExp("created <= {:created}", dbx.Params{"created": minDate})).
		AndWhere(dbx.HashExp{
			"read":      false,
			"processed": false,
		}).
		OrderBy("user ASC", "created ASC").
		Limit(int64(limit))

	if err := query.All(&notifications); err != nil {
		return nil, err
	}

	result := map[string][]*core.Record{}

	for _, n := range notifications {
		user := n.GetString("user")
		result[user] = append(result[user], n)
	}

	return result, nil
}
