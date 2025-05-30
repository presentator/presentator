package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestLinkShare(t *testing.T) {
	t.Parallel()

	validDataFunc := func() *strings.Reader {
		return strings.NewReader(`{
			"message": "test",
			"emails": ["share1@example.com", "share2@example.com"]
		}`)
	}

	failAfterTestFunc := func(t testing.TB, app *tests.TestApp, res *http.Response) {
		if app.TestMailer.TotalSend() != 0 {
			t.Fatalf("Expected %d sent emails, got %d", 0, app.TestMailer.TotalSend())
		}
	}

	scenarios := []tests.ApiScenario{
		{
			Name:            "guest with existing link and valid data",
			Method:          http.MethodPost,
			URL:             "/api/pr/share/5kgzv7br5487j0d",
			Body:            validDataFunc(),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  401,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
			AfterTestFunc:   failAfterTestFunc,
		},
		{
			Name:   "link auth with existing link and valid data",
			Method: http.MethodPost,
			URL:    "/api/pr/share/5kgzv7br5487j0d",
			Body:   validDataFunc(),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
			AfterTestFunc:   failAfterTestFunc,
		},
		{
			Name:   "non-owner user auth with existing link and valid data",
			Method: http.MethodPost,
			URL:    "/api/pr/share/5kgzv7br5487j0d",
			Body:   validDataFunc(),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
			AfterTestFunc:   failAfterTestFunc,
		},
		{
			Name:   "owner user auth with existing link and empty data",
			Method: http.MethodPost,
			URL:    "/api/pr/share/5kgzv7br5487j0d",
			Body:   strings.NewReader("{}"),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"emails":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{"*": 0},
			AfterTestFunc:  failAfterTestFunc,
		},
		{
			Name:   "owner user auth with existing link and invalid data",
			Method: http.MethodPost,
			URL:    "/api/pr/share/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"message": "` + strings.Repeat("a", 256) + `", "emails":["test@example.com","invalid"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"emails":{"1":{"code":"validation_is_email"`,
				`"message":{"code":"validation_length_too_long"`,
			},
			ExpectedEvents: map[string]int{"*": 0},
			AfterTestFunc:  failAfterTestFunc,
		},
		{
			Name:   "owner user auth with existing link and valid data",
			Method: http.MethodPost,
			URL:    "/api/pr/share/5kgzv7br5487j0d",
			Body:   validDataFunc(),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":            0,
				"OnMailerSend": 2,
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				if app.TestMailer.TotalSend() != 2 {
					t.Fatalf("Expected %d sent emails, got %d", 2, app.TestMailer.TotalSend())
				}

				htmlExpectations := []string{
					"Multiple owners project",
					strings.TrimRight(app.Settings().Meta.AppURL, "/") + "/#/test1",
				}

				for _, content := range htmlExpectations {
					if !strings.Contains(app.TestMailer.LastMessage().HTML, content) {
						t.Fatalf("Expected to find \n%q\nin\n%s", content, app.TestMailer.LastMessage().HTML)
					}
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
