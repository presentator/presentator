package presentator

import (
	"net/http"
	"testing"

	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tests"
)

func TestOptions(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:           "guest",
			Method:         http.MethodGet,
			URL:            "/api/pr/options",
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appURL":"http://localhost:8090"`,
				`"termsURL":""`,
				`"allowHotspotsURL":false`,
				`"maxScreenFileSize":7340032`,
			},
			ExpectedEvents: map[string]int{"*": 0},
		},
		{
			Name:   "user auth",
			Method: http.MethodGet,
			URL:    "/api/pr/options",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appURL":"http://localhost:8090"`,
				`"termsURL":""`,
				`"allowHotspotsURL":false`,
				`"maxScreenFileSize":7340032`,
			},
			ExpectedEvents: map[string]int{"*": 0},
		},
		{
			Name:   "link auth",
			Method: http.MethodGet,
			URL:    "/api/pr/options",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"links":{}`,
				`"appName":"Presentator"`,
				`"appURL":"http://localhost:8090"`,
				`"termsURL":""`,
				`"allowHotspotsURL":false`,
				`"maxScreenFileSize":7340032`,
			},
			ExpectedEvents: map[string]int{"*": 0},
		},
		{
			Name:           "custom store options",
			Method:         http.MethodGet,
			URL:            "/api/pr/options",
			TestAppFactory: setupTestApp,
			BeforeTestFunc: func(t testing.TB, app *tests.TestApp, e *core.ServeEvent) {
				app.Settings().Meta.AppName = "test_name"
				app.Settings().Meta.AppURL = "test_url/"
				app.Store().Set(OptionTermsURL, "test_term/")
				app.Store().Set(OptionAllowHotspotsURL, true)
				app.Store().Set(OptionFooterLinks, map[string]string{"abc": "123"})
			},
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"links":{"abc":"123"}`,
				`"appName":"test_name"`,
				`"appURL":"test_url/"`,
				`"termsURL":"test_term/"`,
				`"allowHotspotsURL":true`,
				`"maxScreenFileSize":7340032`,
			},
			ExpectedEvents: map[string]int{"*": 0},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
