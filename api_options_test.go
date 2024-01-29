package presentator

import (
	"net/http"
	"testing"

	"github.com/labstack/echo/v5"
	"github.com/pocketbase/pocketbase/tests"
)

func TestOptions(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "guest",
			Method:          http.MethodGet,
			Url:             "/api/pr/options",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appUrl":"http://localhost:8090"`,
				`"termsUrl":""`,
				`"allowHotspotsUrl":false`,
			},
		},
		{
			Name:   "user auth",
			Method: http.MethodGet,
			Url:    "/api/pr/options",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appUrl":"http://localhost:8090"`,
				`"termsUrl":""`,
				`"allowHotspotsUrl":false`,
			},
		},
		{
			Name:   "link auth",
			Method: http.MethodGet,
			Url:    "/api/pr/options",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appUrl":"http://localhost:8090"`,
				`"termsUrl":""`,
				`"allowHotspotsUrl":false`,
			},
		},
		{
			Name:            "custom store options",
			Method:          http.MethodGet,
			Url:             "/api/pr/options",
			TestAppFactory:  setupTestApp,
			BeforeTestFunc: func(t *testing.T, app *tests.TestApp, e *echo.Echo) {
				app.Settings().Meta.AppName = "test_name"
				app.Settings().Meta.AppUrl = "test_url/"
				app.Store().Set(OptionTermsUrl, "test_term/")
				app.Store().Set(OptionAllowHotspotsUrl, true)
				app.Store().Set(OptionFooterLinks, map[string]string{"abc": "123"})
			},
			ExpectedStatus:  200,
			ExpectedContent: []string{
				`"links":{"abc":"123"}`,
				`"appName":"test_name"`,
				`"appUrl":"test_url/"`,
				`"termsUrl":"test_term/"`,
				`"allowHotspotsUrl":true`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
