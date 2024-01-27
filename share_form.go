package presentator

import (
	"errors"
	"fmt"
	"net/mail"
	"strings"

	validation "github.com/go-ozzo/ozzo-validation/v4"
	"github.com/go-ozzo/ozzo-validation/v4/is"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/models"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/pocketbase/pocketbase/tools/mailer"
)

// newShareForm creates new link share form.
func newShareForm(app core.App, project *models.Record, link *models.Record) *ShareForm {
	return &ShareForm{
		app:     app,
		project: project,
		link:    link,
	}
}

// ShareForm handles a single link share submission.
type ShareForm struct {
	app     core.App
	project *models.Record
	link    *models.Record

	Message string   `form:"message" json:"message"`
	Emails  []string `form:"emails" json:"emails"`
}

// Validate makes the form validatable by implementing [validation.Validatable] interface.
func (form *ShareForm) Validate() error {
	return validation.ValidateStruct(form,
		validation.Field(&form.Message, validation.Length(0, 255)),
		validation.Field(&form.Emails,
			validation.Required,
			validation.Length(1, 10), // max 10 emails for now
			validation.Each(validation.Length(0, 255), is.EmailFormat),
		),
	)
}

// Submit validates the form and send an email with the project link to each form.Emails.
//
// If an error occur during the mail submission the operation doesn't stop.
// All mail send failures are returned as a single joined error.
func (form *ShareForm) Submit() error {
	if err := form.Validate(); err != nil {
		return err
	}

	previewUrl := strings.TrimRight(form.app.Settings().Meta.AppUrl, "/") + "/#/" +form.link.Username()

	data := struct {
		*baseMailData
		Project    *models.Record
		PreviewUrl string
		Message    string
	}{
		baseMailData: newBaseMailData(form.app),
		Project:      form.project,
		PreviewUrl:   previewUrl,
		Message:      form.Message,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"share.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render share template: %w", err)
	}

	var errs []error

	mailClient := form.app.NewMailClient()

	uniqueEmails := list.ToUniqueStringSlice(form.Emails)

	for _, email := range uniqueEmails {
		message := &mailer.Message{
			From: mail.Address{
				Address: form.app.Settings().Meta.SenderAddress,
				Name:    form.app.Settings().Meta.SenderName,
			},
			To:      []mail.Address{{Address: email}},
			Subject: form.app.Settings().Meta.AppName + " - " + form.project.GetString("title") + " view",
			HTML:    html,
		}

		if err := mailClient.Send(message); err != nil {
			errs = append(errs, err)
		}
	}

	if len(errs) > 0 {
		return errors.Join(errs...)
	}

	return err
}
