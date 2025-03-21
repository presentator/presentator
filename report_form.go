package presentator

import (
	"fmt"
	"net/mail"
	"strings"

	validation "github.com/go-ozzo/ozzo-validation/v4"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/mailer"
)

// newReportForm creates new ReportForm request form.
func newReportForm(app core.App, link *core.Record) *ReportForm {
	return &ReportForm{
		app:  app,
		link: link,
	}
}

// ReportForm handles a design report submission.
type ReportForm struct {
	app  core.App
	link *core.Record

	Message string `form:"message" json:"message"`
}

// Validate makes the form validatable by implementing [validation.Validatable] interface.
func (form *ReportForm) Validate() error {
	return validation.ValidateStruct(form,
		validation.Field(&form.Message, validation.Length(0, 255)),
	)
}

// Submit validates the form and send a report email.
func (form *ReportForm) Submit() error {
	if err := form.Validate(); err != nil {
		return err
	}

	project, err := form.app.FindRecordById("projects", form.link.GetString("project"))
	if err != nil {
		return fmt.Errorf("missing link project: %w", err)
	}

	previewURL := strings.TrimRight(form.app.Settings().Meta.AppURL, "/") + "/#/" + form.link.GetString("username")

	data := struct {
		*baseMailData
		Project    *core.Record
		PreviewURL string
		Message    string
	}{
		baseMailData: newBaseMailData(form.app),
		Project:      project,
		PreviewURL:   previewURL,
		Message:      form.Message,
	}

	html, err := templates.LoadFS(
		templatesFS,
		"layout.html",
		"report.html",
	).Render(data)
	if err != nil {
		return fmt.Errorf("failed to render report template: %w", err)
	}

	message := &mailer.Message{
		From: mail.Address{
			Address: form.app.Settings().Meta.SenderAddress,
			Name:    form.app.Settings().Meta.SenderName,
		},
		To:      []mail.Address{{Address: form.app.Settings().Meta.SenderAddress}},
		Subject: "Design report violation",
		HTML:    html,
	}

	return form.app.NewMailClient().Send(message)
}
