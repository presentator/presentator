package presentator

import (
	"net/http"
	"strings"
	"testing"
	"time"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/tests"
)

func TestProjectsList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/projects/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with no projects",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with projects",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"776t18b1tmishx2"`,
				`"id":"kk69rwtejro96iz"`,
				`"id":"t45bjlayvsx2yj0"`,
				`"id":"bhzozkkxldbr9ui"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectsView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for different project",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records/ddn9jjr12d1kms7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the viewed project",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"t45bjlayvsx2yj0"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodGet,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"t45bjlayvsx2yj0"`},
			ExpectedEvents: map[string]int{
				"OnRecordViewRequest": 1,
				// lastViewed prefs update
				"OnModelAfterUpdate":  1,
				"OnModelBeforeUpdate": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectsCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			Url:             "/api/collections/projects/records",
			Body:            strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user nonexisting in the users list",
			Method: http.MethodPost,
			Url:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user existing in the users list",
			Method: http.MethodPost,
			Url:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l", "0dwe3z1d444g9mo"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			Delay:          100 * time.Millisecond, // short delay for the emails goroutine
			ExpectedContent: []string{
				`"title":"new"`,
				`"users":["nwl39aj35c02p7l","0dwe3z1d444g9mo"]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          3, // +user preferences
				"OnModelBeforeCreate":         3,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				if len(app.TestMailer.SentMessages) != 1 || len(app.TestMailer.LastMessage.To) != 1 || app.TestMailer.LastMessage.To[0].Address != "test1@example.com" {
					t.Fatalf("Expected exactly 1 assigned project email to %s: %v", "test1@example.com", app.TestMailer.LastMessage)
				}
			},
		},
		{
			Name:   "auth as user existing in the users list but with invalid data",
			Method: http.MethodPost,
			Url:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"", "users":["nwl39aj35c02p7l", "0dwe3z1d444g9mo"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"title":{"code":"validation_required"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectsUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:            strings.NewReader(`{"archived":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as non-owner user",
			Method: http.MethodPatch,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as owner user",
			Method: http.MethodPatch,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			Delay:          100 * time.Millisecond, // short delay for the emails goroutine
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"t45bjlayvsx2yj0"`,
				`"archived":true`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				if total := len(app.TestMailer.SentMessages); total != 0 {
					t.Fatalf("Expected 0 notification emails, got %d:\n%v", total, app.TestMailer.SentMessages)
				}
			},
		},
		{
			Name:   "auth as owner user removing one owner and adding another",
			Method: http.MethodPatch,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"users":["nwl39aj35c02p7l","0dwe3z1d444g9mo"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			Delay:          100 * time.Millisecond,
			ExpectedContent: []string{
				`"id":"t45bjlayvsx2yj0"`,
				`"users":["nwl39aj35c02p7l","0dwe3z1d444g9mo"]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
				// because of user prefs
				"OnModelAfterCreate":  1,
				"OnModelBeforeCreate": 1,
				"OnModelAfterDelete":  1,
				"OnModelBeforeDelete": 1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				// ensure that the old user pref was removed
				oldUserPref, _ := app.Dao().FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": "t45bjlayvsx2yj0",
					"user":    "7rs5wkqeb5gggmn",
				})
				if oldUserPref != nil {
					t.Fatalf("Expected old project user preference to be deleted, found pref %q", oldUserPref.Id)
				}

				// ensure that the new user pref was created
				_, newUserPrefErr := app.Dao().FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": "t45bjlayvsx2yj0",
					"user":    "0dwe3z1d444g9mo",
				})
				if newUserPrefErr != nil {
					t.Fatal("Expected the new project user preference to be created, got nil", newUserPrefErr)
				}

				if total := len(app.TestMailer.SentMessages); total != 2 {
					t.Fatalf("Expected %d notification emails, got %d", 2, total)
				}

				var hasAssignedEmail bool
				for _, m := range app.TestMailer.SentMessages {
					if m.To[0].Address == "test4@example.com" &&
						strings.Contains(m.Subject, "project access granted") {
						hasAssignedEmail = true
						break
					}
				}
				if !hasAssignedEmail {
					t.Fatalf("Expected assigned project email to be sent.")
				}

				var hasUnassignedEmail bool
				for _, m := range app.TestMailer.SentMessages {
					if m.To[0].Address == "test2@example.com" &&
						strings.Contains(m.Subject, "project access removed") {
						hasUnassignedEmail = true
						break
					}
				}
				if !hasUnassignedEmail {
					t.Fatalf("Expected unassigned project email to be sent.")
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectsDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as non-owner user",
			Method: http.MethodDelete,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as owner user",
			Method: http.MethodDelete,
			Url:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			Delay:          100 * time.Millisecond, // short delay for the emails goroutine
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				// include cascade deleted rels
				"OnModelAfterDelete":  24,
				"OnModelBeforeDelete": 24,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				if total := len(app.TestMailer.SentMessages); total != 0 {
					t.Fatalf("Expected 0 notification emails, got %d:\n%v", total, app.TestMailer.SentMessages)
				}

				// check if the project was actually deleted
				p, _ := app.Dao().FindRecordById("projects", "t45bjlayvsx2yj0")
				if p != nil {
					t.Fatalf("Expected project %q to be deleted", p.Id)
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
