package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestReport(t *testing.T) {
	t.Parallel()

	validDataFunc := func() *strings.Reader {
		return strings.NewReader(`{"message": "test"}`)
	}

	failAfterTestFunc := func(t *testing.T, app *tests.TestApp, res *http.Response) {
		if app.TestMailer.TotalSend != 0 {
			t.Fatalf("Expected %d sent emails, got %d", 0, app.TestMailer.TotalSend)
		}
	}

	scenarios := []tests.ApiScenario{
		{
			Name:            "guest and valid data",
			Method:          http.MethodPost,
			Url:             "/api/pr/report",
			Body:            validDataFunc(),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  401,
			ExpectedContent: []string{`"data":{}`},
			AfterTestFunc:   failAfterTestFunc,
		},
		{
			Name:   "user auth and valid data",
			Method: http.MethodPost,
			Url:    "/api/pr/report",
			Body:   validDataFunc(),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			AfterTestFunc:   failAfterTestFunc,
		},
		{
			Name:   "link auth and invalid data",
			Method: http.MethodPost,
			Url:    "/api/pr/report",
			Body:   strings.NewReader(`{"message":"` + strings.Repeat("a", 256) + `"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{"message":{"code":"validation_length_too_long"`,
			},
			AfterTestFunc: failAfterTestFunc,
		},
		{
			Name:   "link auth and invalid data",
			Method: http.MethodPost,
			Url:    "/api/pr/report",
			Body:   validDataFunc(),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				if app.TestMailer.TotalSend != 1 {
					t.Fatalf("Expected %d sent emails, got %d", 1, app.TestMailer.TotalSend)
				}

				if len(app.TestMailer.LastMessage.To) != 1 ||
					app.TestMailer.LastMessage.To[0].Address != app.Settings().Meta.SenderAddress {
					t.Fatalf("Expected exactly 1 recipient with email %q, got %v", app.Settings().Meta.SenderAddress, app.TestMailer.LastMessage.To)
				}

				htmlExpectations := []string{
					"Multiple owners project",
					strings.TrimRight(app.Settings().Meta.AppUrl, "/") + "/#/test1",
				}

				for _, content := range htmlExpectations {
					if !strings.Contains(app.TestMailer.LastMessage.HTML, content) {
						t.Fatalf("Expected to find \n%q\nin\n%s", content, app.TestMailer.LastMessage.HTML)
					}
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
