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
			URL:             "/api/collections/projects/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
			},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
			},
		},
		{
			Name:   "auth as user with no projects",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
			},
		},
		{
			Name:   "auth as user with projects",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"776t18b1tmishx2"`,
				`"id":"kk69rwtejro96iz"`,
				`"id":"t45bjlayvsx2yj0"`,
				`"id":"bhzozkkxldbr9ui"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       4,
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
			URL:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link for different project",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records/ddn9jjr12d1kms7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link for the viewed project",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"t45bjlayvsx2yj0"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodGet,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"t45bjlayvsx2yj0"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				// lastViewed prefs update
				"OnModelValidate":            1,
				"OnModelUpdate":              1,
				"OnModelUpdateExecute":       1,
				"OnModelAfterUpdateSuccess":  1,
				"OnRecordValidate":           1,
				"OnRecordUpdate":             1,
				"OnRecordUpdateExecute":      1,
				"OnRecordAfterUpdateSuccess": 1,
				"OnRecordEnrich":             1,
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
			URL:             "/api/collections/projects/records",
			Body:            strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			URL:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user nonexisting in the users list",
			Method: http.MethodPost,
			URL:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user existing in the users list",
			Method: http.MethodPost,
			URL:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"new", "users":["nwl39aj35c02p7l", "0dwe3z1d444g9mo"]}`),
			Headers: map[string]string{
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
				"*":                          0,
				"OnRecordCreateRequest":      1,
				"OnModelCreate":              3, // +user preferences
				"OnModelValidate":            3,
				"OnModelCreateExecute":       3,
				"OnModelAfterCreateSuccess":  3,
				"OnRecordCreate":             3,
				"OnRecordValidate":           3,
				"OnRecordCreateExecute":      3,
				"OnRecordAfterCreateSuccess": 3,
				"OnRecordEnrich":             1,
				"OnMailerSend":               1,
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				if app.TestMailer.TotalSend() != 1 || len(app.TestMailer.LastMessage().To) != 1 || app.TestMailer.LastMessage().To[0].Address != "test1@example.com" {
					t.Fatalf("Expected exactly 1 assigned project email to %s: %v", "test1@example.com", app.TestMailer.LastMessage())
				}
			},
		},
		{
			Name:   "auth as user existing in the users list but with invalid data",
			Method: http.MethodPost,
			URL:    "/api/collections/projects/records",
			Body:   strings.NewReader(`{"title":"", "users":["nwl39aj35c02p7l", "0dwe3z1d444g9mo"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"title":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{
				"*":                        0,
				"OnRecordCreateRequest":    1,
				"OnModelCreate":            1,
				"OnModelValidate":          1,
				"OnModelAfterCreateError":  1,
				"OnRecordCreate":           1,
				"OnRecordValidate":         1,
				"OnRecordAfterCreateError": 1,
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
			URL:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:            strings.NewReader(`{"archived":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as non-owner user",
			Method: http.MethodPatch,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as owner user",
			Method: http.MethodPatch,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"archived":true}`),
			Headers: map[string]string{
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
				"*":                          0,
				"OnRecordUpdateRequest":      1,
				"OnModelValidate":            1,
				"OnModelUpdate":              1,
				"OnModelUpdateExecute":       1,
				"OnModelAfterUpdateSuccess":  1,
				"OnRecordValidate":           1,
				"OnRecordUpdate":             1,
				"OnRecordUpdateExecute":      1,
				"OnRecordAfterUpdateSuccess": 1,
				"OnRecordEnrich":             1,
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				if total := app.TestMailer.TotalSend(); total != 0 {
					t.Fatalf("Expected 0 notification emails, got %d:\n%v", total, app.TestMailer.Messages())
				}
			},
		},
		{
			Name:   "auth as owner user removing one owner and adding another",
			Method: http.MethodPatch,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Body:   strings.NewReader(`{"users":["nwl39aj35c02p7l","0dwe3z1d444g9mo"]}`),
			Headers: map[string]string{
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
				"*":                          0,
				"OnRecordUpdateRequest":      1,
				"OnMailerSend":               2,
				"OnModelValidate":            2, // + user prefs create
				"OnModelUpdate":              1,
				"OnModelUpdateExecute":       1,
				"OnModelAfterUpdateSuccess":  1,
				"OnRecordValidate":           2,
				"OnRecordUpdate":             1,
				"OnRecordUpdateExecute":      1,
				"OnRecordAfterUpdateSuccess": 1,
				"OnRecordEnrich":             1,
				// because of user prefs
				"OnModelCreate":              1,
				"OnModelCreateExecute":       1,
				"OnModelAfterCreateSuccess":  1,
				"OnModelDelete":              1,
				"OnModelDeleteExecute":       1,
				"OnModelAfterDeleteSuccess":  1,
				"OnRecordCreate":             1,
				"OnRecordCreateExecute":      1,
				"OnRecordAfterCreateSuccess": 1,
				"OnRecordDelete":             1,
				"OnRecordDeleteExecute":      1,
				"OnRecordAfterDeleteSuccess": 1,
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				// ensure that the old user pref was removed
				oldUserPref, _ := app.FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": "t45bjlayvsx2yj0",
					"user":    "7rs5wkqeb5gggmn",
				})
				if oldUserPref != nil {
					t.Fatalf("Expected old project user preference to be deleted, found pref %q", oldUserPref.Id)
				}

				// ensure that the new user pref was created
				_, newUserPrefErr := app.FindFirstRecordByFilter("projectUserPreferences", "project={:project} && user={:user}", dbx.Params{
					"project": "t45bjlayvsx2yj0",
					"user":    "0dwe3z1d444g9mo",
				})
				if newUserPrefErr != nil {
					t.Fatal("Expected the new project user preference to be created, got nil", newUserPrefErr)
				}

				if total := app.TestMailer.TotalSend(); total != 2 {
					t.Fatalf("Expected %d notification emails, got %d", 2, total)
				}

				var hasAssignedEmail bool
				for _, m := range app.TestMailer.Messages() {
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
				for _, m := range app.TestMailer.Messages() {
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
			URL:             "/api/collections/projects/records/t45bjlayvsx2yj0",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as non-owner user",
			Method: http.MethodDelete,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as owner user",
			Method: http.MethodDelete,
			URL:    "/api/collections/projects/records/t45bjlayvsx2yj0",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			Delay:          100 * time.Millisecond, // short delay for the emails goroutine
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordDeleteRequest": 1,
				// include cascade deleted rels
				"OnModelDelete":              24,
				"OnModelDeleteExecute":       24,
				"OnModelAfterDeleteSuccess":  24,
				"OnRecordDelete":             24,
				"OnRecordDeleteExecute":      24,
				"OnRecordAfterDeleteSuccess": 24,
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				if total := app.TestMailer.TotalSend(); total != 0 {
					t.Fatalf("Expected 0 notification emails, got %d:\n%v", total, app.TestMailer.TotalSend())
				}

				// check if the project was actually deleted
				p, _ := app.FindRecordById("projects", "t45bjlayvsx2yj0")
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
