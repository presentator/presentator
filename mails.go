package presentator

import (
	"embed"
	"encoding/xml"
	"errors"
	"fmt"
	gotemplate "html/template"
	"io/fs"
	"net/mail"
	"path"
	"strings"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/mailer"
	"github.com/pocketbase/pocketbase/tools/template"
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
		"sub": func(a, b int) int {
			return a - b
		},
		"add": func(a, b int) int {
			return a + b
		},
	})
}

type baseMailData struct {
	AppName         string
	AppURL          string
	AppLogoURL      string
	AppPrimaryColor string
}

func newBaseMailData(app core.App) *baseMailData {
	appURL := strings.TrimRight(app.Settings().Meta.AppURL, "/")

	// @todo consider making it dynamic once custom Admin UI settings fields are added.
	logoURL := appURL + "/images/logo.png"
	primaryColor := "#4f4cf6"

	return &baseMailData{
		AppName:         app.Settings().Meta.AppName,
		AppURL:          appURL,
		AppLogoURL:      logoURL,
		AppPrimaryColor: primaryColor,
	}
}

func registerSystemEmails(app core.App) {
	layoutWrap := func(e *core.MailerRecordEvent) error {
		body, err := extractBody(e.Message.HTML)
		if err != nil {
			return err
		}

		data := struct {
			*baseMailData
			HTMLContent gotemplate.HTML
		}{
			baseMailData: newBaseMailData(app),
			HTMLContent:  gotemplate.HTML(body),
		}

		html, err := templates.LoadFS(templatesFS, "layout.html", "system.html").Render(data)
		if err != nil {
			return err
		}

		e.Message.HTML = html

		return e.Next()
	}

	app.OnMailerRecordAuthAlertSend("users").BindFunc(layoutWrap)
	app.OnMailerRecordOTPSend("users").BindFunc(layoutWrap)
	app.OnMailerRecordVerificationSend("users").BindFunc(layoutWrap)
	app.OnMailerRecordPasswordResetSend("users").BindFunc(layoutWrap)
	app.OnMailerRecordEmailChangeSend("users").BindFunc(layoutWrap)
}

func sendAddedOwnerEmail(app core.App, project *core.Record, initiator *core.Record, userId string) error {
	user, err := app.FindRecordById("users", userId)
	if err != nil {
		return err
	}

	actionURL := strings.TrimRight(app.Settings().Meta.AppURL, "/") + path.Join("/#/projects", project.Id, "prototypes")

	data := struct {
		*baseMailData
		Project   *core.Record
		Initiator *core.Record
		ActionURL string
	}{
		baseMailData: newBaseMailData(app),
		Project:      project,
		Initiator:    initiator,
		ActionURL:    actionURL,
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

func sendRemovedOwnerEmail(app core.App, project *core.Record, initiator *core.Record, userId string) error {
	user, err := app.FindRecordById("users", userId)
	if err != nil {
		return err
	}

	data := struct {
		*baseMailData
		Project   *core.Record
		Initiator *core.Record
	}{
		baseMailData: newBaseMailData(app),
		Project:      project,
		Initiator:    initiator,
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

func sendUnreadEmail(app core.App, notifications []*core.Record, userId string) error {
	total := len(notifications)
	if total == 0 {
		return nil
	}

	user, err := app.FindRecordById("users", userId)
	if err != nil {
		return err
	}

	type unreadItem struct {
		Comment *core.Record
		Screen  *core.Record
		Author  string
	}

	unreads := make([]unreadItem, 0, len(notifications))
	for _, n := range notifications {
		comment, err := app.FindRecordById("comments", n.GetString("comment"))
		if err != nil {
			return fmt.Errorf("failed to fetch notification comment %q: %w", n.GetString("comment"), err)
		}

		screen, err := app.FindRecordById("screens", comment.GetString("screen"))
		if err != nil {
			return fmt.Errorf("failed to fetch notification screen %q: %w", comment.GetString("screen"), err)
		}

		author, err := getCommentUserIdentifier(app, comment)
		if err != nil {
			return err
		}

		unreads = append(unreads, unreadItem{
			Comment: comment,
			Screen:  screen,
			Author:  author,
		})
	}

	data := struct {
		*baseMailData
		ActionURL string
		Unreads   []unreadItem
	}{
		baseMailData: newBaseMailData(app),
		ActionURL:    strings.TrimRight(app.Settings().Meta.AppURL, "/") + "/#/projects?notifications=1",
		Unreads:      unreads,
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

func sendGuestsEmail(app core.App, comment *core.Record) error {
	// find guests that participate in the comment thread to notify them
	primaryId := comment.GetString("replyTo")
	if primaryId == "" {
		primaryId = comment.Id
	}
	otherGuestComments, err := app.FindRecordsByFilter("comments", "screen={:screenId} && user='' && guestEmail!={:guestEmail} && (id={:primaryId} || replyTo={:primaryId})", "", 0, 0, dbx.Params{
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
			guestUser, _ := app.FindAuthRecordByEmail("users", email)
			if guestUser == nil || guestUser.GetBool("allowEmailNotifications") {
				guestEmails = append(guestEmails, email)
			}
		}
	}

	if len(guestEmails) == 0 {
		return nil // no guests to notify
	}

	screen, err := app.FindRecordById("screens", comment.GetString("screen"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen %q: %w", comment.GetString("screen"), err)
	}

	prototype, err := app.FindRecordById("prototypes", screen.GetString("prototype"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen prototype %q: %w", comment.GetString("screen"), err)
	}

	project, err := app.FindRecordById("projects", prototype.GetString("project"))
	if err != nil {
		return fmt.Errorf("failed to fetch comment screen project %q: %w", prototype.GetString("project"), err)
	}

	var actionURL string

	// try to find the first related link that allow comments
	link, _ := app.FindFirstRecordByFilter(
		"links",
		"project={:projectId} && allowComments=true && (onlyPrototypes:length=0 || onlyPrototypes.id?={:prototypeId})",
		dbx.Params{
			"projectId":   project.Id,
			"prototypeId": screen.GetString("prototype"),
		},
	)
	if link != nil {
		actionURL = strings.TrimRight(app.Settings().Meta.AppURL, "/") + path.Join(
			"/#/",
			link.GetString("username"),
			"/prototypes/",
			screen.GetString("prototype"),
			"/screens/",
			screen.Id,
		) + "?mode=comments&commentId=" + comment.Id
	}

	userIdentifier, err := getCommentUserIdentifier(app, comment)
	if err != nil {
		return err
	}

	data := struct {
		*baseMailData
		ActionURL      string
		Comment        *core.Record
		Project        *core.Record
		Screen         *core.Record
		UserIdentifier string
	}{
		baseMailData:   newBaseMailData(app),
		ActionURL:      actionURL,
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
func getCommentUserIdentifier(app core.App, comment *core.Record) (string, error) {
	var userIdentifier string

	if userId := comment.GetString("user"); userId != "" {
		user, err := app.FindRecordById("users", userId)
		if err != nil {
			return "", fmt.Errorf("failed to comment user %q: %w", userId, err)
		}

		userIdentifier = user.GetString("name")
		if userIdentifier == "" {
			userIdentifier = user.GetString("username")
		}
	} else {
		userIdentifier = comment.GetString("guestEmail")
	}

	return userIdentifier, nil
}

func extractBody(htmlContent string) (string, error) {
	parsedHTML := struct {
		Body struct {
			Content string `xml:",innerxml"`
		} `xml:"body"`
	}{}

	err := xml.NewDecoder(strings.NewReader(htmlContent)).Decode(&parsedHTML)
	if err != nil {
		return "", err
	}

	return parsedHTML.Body.Content, nil
}
