package presentator

import (
	validation "github.com/go-ozzo/ozzo-validation/v4"
	"github.com/go-ozzo/ozzo-validation/v4/is"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tools/router"
)

// transitions is list of all supported overlay transition values.
//
// note: any because of validation.In()
var transitions = []any{
	"fade",
	"slide-left",
	"slide-right",
	"slide-top",
	"slide-bottom",
}

// overlayPositions is list of all supported overlay position values.
//
// note: any because of validation.In()
var overlayPositions = []any{
	"top-left",
	"top-right",
	"bottom-left",
	"bottom-right",
	"top-center",
	"bottom-center",
	"centered",
}

type SettingsValidator interface {
	Validate(app core.App) error
}

func filterAndValidateHotspotSettings(app core.App, record *core.Record) error {
	const baseMessage = "Invalid hotspot settings."

	var settings SettingsValidator

	switch record.GetString("type") {
	case "screen":
		settings = &settingsScreen{}
	case "overlay":
		settings = &settingsOverlay{}
	case "back":
		settings = &settingsBack{}
	case "prev":
		settings = &settingsPrev{}
	case "next":
		settings = &settingsNext{}
	case "url":
		settings = &settingsURL{}
	case "scroll":
		settings = &settingsScroll{}
	case "note":
		settings = &settingsNote{}
	default:
		return router.NewBadRequestError(baseMessage, map[string]validation.Error{
			"type": validation.NewError("invalid_type", "Invalid hotspot type."),
		})
	}

	if err := record.UnmarshalJSONField("settings", settings); err != nil {
		return router.NewBadRequestError(baseMessage, map[string]validation.Error{
			"settings": validation.NewError("invalid_settings_data", "Invalid hotspot settings data."),
		})
	}

	if err := settings.Validate(app); err != nil {
		return router.NewBadRequestError(baseMessage, map[string]any{
			"settings": err,
		})
	}

	// normalize and save only the struct defined fields
	record.Set("settings", settings)

	return nil
}

func checkScreen(app core.App) validation.RuleFunc {
	return func(value any) error {
		v, _ := value.(string)
		if v == "" {
			return nil // nothing to check
		}

		if _, err := app.FindRecordById("screens", v); err != nil {
			return validation.NewError("invalid_screen", "Invalid or missing screen.")
		}

		return nil
	}
}

// -------------------------------------------------------------------

type settingsScreen struct {
	Screen     string `json:"screen"`
	Transition string `json:"transition"`
}

func (s *settingsScreen) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Screen, validation.Required, validation.By(checkScreen(app))),
		validation.Field(&s.Transition, validation.In(transitions...)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsOverlay)(nil)

type settingsOverlay struct {
	Screen          string  `json:"screen"`
	Transition      string  `json:"transition"`
	OverlayPosition string  `json:"overlayPosition"`
	FixOverlay      bool    `json:"fixOverlay"`
	OutsideClose    bool    `json:"outsideClose"`
	OffsetTop       float64 `json:"offsetTop"`
	OffsetBottom    float64 `json:"offsetBottom"`
	OffsetLeft      float64 `json:"offsetLeft"`
	OffsetRight     float64 `json:"offsetRight"`
}

func (s *settingsOverlay) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Screen, validation.Required, validation.By(checkScreen(app))),
		validation.Field(&s.OverlayPosition, validation.Required, validation.In(overlayPositions...)),
		validation.Field(&s.Transition, validation.In(transitions...)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsBack)(nil)

type settingsBack struct {
	Transition string `json:"transition"`
}

func (s *settingsBack) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Transition, validation.In(transitions...)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsNext)(nil)

type settingsNext struct {
	Transition string `json:"transition"`
}

func (s *settingsNext) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Transition, validation.In(transitions...)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsPrev)(nil)

type settingsPrev struct {
	Transition string `json:"transition"`
}

func (s *settingsPrev) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Transition, validation.In(transitions...)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsURL)(nil)

type settingsURL struct {
	URL string `json:"url"`
}

func (s *settingsURL) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.URL, validation.Required, is.URL),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsScroll)(nil)

type settingsScroll struct {
	ScrollTop  float64 `json:"scrollTop"`
	ScrollLeft float64 `json:"scrollLeft"`
}

func (s *settingsScroll) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.ScrollTop, validation.Min(0.0)),
		validation.Field(&s.ScrollLeft, validation.Min(0.0)),
	)
}

// -------------------------------------------------------------------

var _ SettingsValidator = (*settingsNote)(nil)

type settingsNote struct {
	Note string `json:"note"`
}

func (s *settingsNote) Validate(app core.App) error {
	return validation.ValidateStruct(s,
		validation.Field(&s.Note, validation.Required, validation.Length(1, 500)),
	)
}
