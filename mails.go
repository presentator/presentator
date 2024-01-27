package presentator

import (
	"embed"
	"errors"
	"fmt"
	"io/fs"
	"net/mail"
	"path"
	"strings"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/daos"
	"github.com/pocketbase/pocketbase/models"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/mailer"
	"github.com/pocketbase/pocketbase/tools/template"
	"github.com/spf13/cast"
)

//go:embed all:templates
var templatesEmbed embed.FS

var templatesFS fs.FS

var templates = template.NewRegistry()

func init() {
	var err error
	templatesFS, err = fs.Sub(templatesEmbed, "templates")
	if err != nil {
		panic(err)
	}

	// register some helper template functions
	templates.AddFuncs(map[string]any{
		"sub": func (a, b int) int {
			return a-b
		},
		"add": func (a, b int) int {
			return a+b
		},
	})
}

type baseMailData struct {
	AppName         string
	AppUrl          string
	AppLogoUrl      string
	AppPrimaryColor string
}

func newBaseMailData(app core.App) *baseMailData {
	appUrl := strings.TrimRight(app.Settings().Meta.AppUrl, "/")

	// @todo consider making it dynamic once custom Admin UI settings fields are added.
	logoUrl := appUrl + "/images/logo.png"
	primaryColor := "#4f4cf6"

	return &baseMailData{
		AppName:         app.Settings().Meta.AppName,
		AppUrl:          appUrl,
		AppLogoUrl:      logoUrl,
		AppPrimaryColor: primaryColor,
	}
}

func registerSystemEmails(app core.App) {
	app.OnMailerBeforeRecordVerificationSend().Add(func(e *core.MailerRecordEvent) error {
		data := struct {
			*baseMailData
			Record    *models.Record
			ActionUrl string
		}{
			baseMailData: newBaseMailData(app),
			Record:       e.Record,
			ActionUrl:    strings.TrimRight(app.Settings().Meta.AppUrl, "/") + "/#/confirm-verification/" + cast.ToString(e.Meta["token"]),
		}

		html, err := templates.LoadFS(
			templatesFS,
			"layout.html",
			"verification.html",
		).Render(data)
		if err != nil {
			return err
		}

		e.Message.HTML = html

		return nil
	})

	app.OnMailerBeforeRecordResetPasswordSend().Add(func(e *core.MailerRecordEvent) error {
		data := struct {
			*baseMailData
			Record    *models.Record
			ActionUrl string
		}{
			baseMailData: newBaseMailData(app),
			Record:       e.Record,
			ActionUrl:    strings.TrimRight(app.Settings().Meta.AppUrl, "/") + "/#/confirm-password-reset/" + cast.ToString(e.Meta["token"]),
		}

		html, err := templates.LoadFS(
			templatesFS,
			"layout.html",
			"password_reset.html",
		).Render(data)
		if err != nil {
			return err
		}

		e.Message.HTML = html

		return nil
	})

	app.OnMailerBeforeRecordChangeEmailSend().Add(func(e *core.MailerRecordEvent) error {
		data := struct {
			*baseMailData
			Record    *models.Record
			ActionUrl string
			NewEmail  string
		}{
			baseMailData: newBaseMailData(app),
			Record:       e.Record,
			ActionUrl:    strings.TrimRight(app.Settings().Meta.AppUrl, "/") + "/#/confirm-email-change/" + cast.ToString(e.Meta["token"]),
			NewEmail:     cast.ToString(e.Meta["newEmail"]),
		}

		html, err := templates.LoadFS(
			templatesFS,
			"layout.html",
			"email_change.html",
		).Render(data)
		if err != nil {
			return err
		}

		e.Message.HTML = html

		return nil
	})
}

func sendAddedOwnerEmail(app core.App, project *models.Record, userId string) error {
	user, err := app.Dao().FindRecordById("users", userId)
	if err != nil {
		return err
	}

	actionUrl := strings.TrimRight(app.Settings().Meta.AppUrl, "/") + path.Join("/#/projects", project.Id, "prototypes")

	data := struct {
		*baseMailData
		Project   *models.Record
		ActionUrl string
	}{
		baseMailData: newBaseMailData(app),
		Project:      project,
		ActionUrl:    actionUrl,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"owner_assigned.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render owner_assigned template: %w", err)
	}

	message := &mailer.Message{
		From: mail.Address{
			Address: app.Settings().Meta.SenderAddress,
			Name:    app.Settings().Meta.SenderName,
		},
		To:      []mail.Address{{Address: user.Email()}},
		Subject: app.Settings().Meta.AppName + " - project access granted",
		HTML:    html,
	}

	return app.NewMailClient().Send(message)
}

func sendRemovedOwnerEmail(app core.App, project *models.Record, userId string) error {
	user, err := app.Dao().FindRecordById("users", userId)
	if err != nil {
		return err
	}

	data := struct {
		*baseMailData
		Project *models.Record
	}{
		baseMailData: newBaseMailData(app),
		Project:      project,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"owner_unassigned.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render owner_unassigned template: %w", err)
	}

	message := &mailer.Message{
		From: mail.Address{
			Address: app.Settings().Meta.SenderAddress,
			Name:    app.Settings().Meta.SenderName,
		},
		To:      []mail.Address{{Address: user.Email()}},
		Subject: app.Settings().Meta.AppName + " - project access removed",
		HTML:    html,
	}

	return app.NewMailClient().Send(message)
}

func sendUnreadEmail(app core.App, notifications []*models.Record, userId string) error {
	total := len(notifications)
	if total == 0 {
		return nil
	}

	user, err := app.Dao().FindRecordById("users", userId)
	if err != nil {
		return err
	}

	type unreadItem struct {
		Comment *models.Record
		Screen *models.Record
		Author string
	}

	unreads := make([]unreadItem, 0, len(notifications))
	for _, n := range notifications {
		comment, err := app.Dao().FindRecordById("comments", n.GetString("comment"))
		if err != nil {
			return fmt.Errorf("failed to fetch notification comment %q: %w", n.GetString("comment"), err)
		}

		screen, err := app.Dao().FindRecordById("screens", comment.GetString("screen"))
		if err != nil {
			return fmt.Errorf("failed to fetch notification screen %q: %w", comment.GetString("screen"), err)
		}

		author, err := getCommentUserIdentifier(app.Dao(), comment)
		if err != nil {
			return err
		}

		unreads = append(unreads, unreadItem{
			Comment: comment,
			Screen: screen,
			Author: author,
		})
	}

	data := struct {
		*baseMailData
		ActionUrl string
		Unreads []unreadItem
	}{
		baseMailData: newBaseMailData(app),
		ActionUrl:    strings.TrimRight(app.Settings().Meta.AppUrl, "/") +  "/#/projects?notifications=1",
		Unreads: unreads,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"unread.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render unread template: %w", err)
	}

	label := "comment"
	if total != 1 {
		label += "s"
	}

	message := &mailer.Message{
		From: mail.Address{
			Address: app.Settings().Meta.SenderAddress,
			Name:    app.Settings().Meta.SenderName,
		},
		To: []mail.Address{{Address: user.Email()}},
		Subject: fmt.Sprintf(
			"%s - %d new %s",
			app.Settings().Meta.AppName,
			total,
			label,
		),
		HTML: html,
	}

	return app.NewMailClient().Send(message)
}

func sendGuestsEmail(app core.App, comment *models.Record) error {
	// find guests that participate in the comment thread to notify them
	primaryId := comment.GetString("replyTo")
	if primaryId == "" {
		primaryId = comment.Id
	}
	otherGuestComments, err := app.Dao().FindRecordsByFilter("comments", "screen={:screenId} && user='' && guestEmail!={:guestEmail} && (id={:primaryId} || replyTo={:primaryId})", "", 0, 0, dbx.Params{
		"screenId":   comment.GetString("screen"),
		"primaryId":  primaryId,
		"guestEmail": comment.GetString("guestEmail"),
	})

	// extract unique guest emails
	guestEmails := make([]string, 0, len(otherGuestComments))
	for _, c := range otherGuestComments {
		email := c.GetString("guestEmail")
		if email != "" && !list.ExistInSlice(email, guestEmails) {
			// check if a user and its preference exist
			guestUser, _ := app.Dao().FindAuthRecordByEmail("users", email)
			if guestUser == nil || guestUser.GetBool("allowEmailNotifications") {
				guestEmails = append(guestEmails, email)
			}
		}
	}

	if len(guestEmails) == 0 {
		return nil // no guests to notify
	}

	screen, err := app.Dao().FindRecordById("screens", comment.GetString("screen"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen %q: %w", comment.GetString("screen"), err)
	}

	prototype, err := app.Dao().FindRecordById("prototypes", screen.GetString("prototype"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen prototype %q: %w", comment.GetString("screen"), err)
	}

	project, err := app.Dao().FindRecordById("projects", prototype.GetString("project"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen project %q: %w", prototype.GetString("project"), err)
	}

	var actionUrl string

	// try to find the first related link that allow comments
	link, _ := app.Dao().FindFirstRecordByFilter(
		"links",
		"project={:projectId} && allowComments=true && (onlyPrototypes:length=0 || onlyPrototypes.id?={:prototypeId})",
		dbx.Params{
			"projectId":   project.Id,
			"prototypeId": screen.GetString("prototype"),
		},
	)
	if link != nil {
		actionUrl = strings.TrimRight(app.Settings().Meta.AppUrl, "/") + path.Join(
			"/#/",
			link.Username(),
			"/prototypes/",
			screen.GetString("prototype"),
			"/screens/",
			screen.Id,
		) + "?mode=comments&commentId=" + comment.Id
	}

	userIdentifier, err := getCommentUserIdentifier(app.Dao(), comment)
	if err != nil {
		return err
	}

	data := struct {
		*baseMailData
		ActionUrl      string
		Comment        *models.Record
		Project        *models.Record
		Screen         *models.Record
		UserIdentifier string
	}{
		baseMailData:   newBaseMailData(app),
		ActionUrl:      actionUrl,
		Comment:        comment,
		Project:        project,
		Screen:         screen,
		UserIdentifier: userIdentifier,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"guest.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render unread template: %w", err)
	}

	var errs []error
	for _, guestEmail := range guestEmails {
		message := &mailer.Message{
			From: mail.Address{
				Address: app.Settings().Meta.SenderAddress,
				Name:    app.Settings().Meta.SenderName,
			},
			To:      []mail.Address{{Address: guestEmail}},
			Subject: app.Settings().Meta.AppName + " - New comment",
			HTML:    html,
		}

		if err := app.NewMailClient().Send(message); err != nil {
			errs = append(errs, err)
		}
	}

	if len(errs) > 0 {
		return fmt.Errorf("failed to notify all guests: %w", errors.Join(errs...))
	}

	return nil
}

// getCommentUserIdentifier returns the user identifier of the comment author.
func getCommentUserIdentifier(dao *daos.Dao, comment *models.Record) (string, error) {
	var userIdentifier string

	if userId := comment.GetString("user"); userId != "" {
		user, err := dao.FindRecordById("users", userId)
		if err != nil {
			return "", fmt.Errorf("failed to comment user %q: %w", userId, err)
		}

		userIdentifier = user.GetString("name")
		if userIdentifier == "" {
			userIdentifier = user.Username()
		}
	} else {
		userIdentifier = comment.GetString("guestEmail")
	}

	return userIdentifier, nil
}
