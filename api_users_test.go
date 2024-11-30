package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/core"
	"github.com/pocketbase/pocketbase/tests"
)

func TestUsersList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "list users as guest",
			Method:          http.MethodGet,
			URL:             "/api/collections/users/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
			},
		},
		{
			Name:   "list users via project link token",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":2`,
				`"username":"test2"`,
				`"username":"test1"`,
				`"test2@example.com"`, // public email
			},
			NotExpectedContent: []string{
				`"test1@example.com"`, // private email
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       2,
			},
		},
		{
			Name:   "list users via user token",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":3`,
				`"username":"test4"`,
				`"username":"test2"`,
				`"username":"test1"`,
				// public emails
				`"test2@example.com"`,
				`"test4@example.com"`,
			},
			NotExpectedContent: []string{
				// unverified
				`"username":"test3"`,
				// private emails
				`"test1@example.com"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       3,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestUsersView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "view user as guest",
			Method:          http.MethodGet,
			URL:             "/api/collections/users/records/nwl39aj35c02p7l",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "view user via project link token (no project owner)",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records/0dwe3z1d444g9mo",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "view user via project link token (project owner)",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"username":"test1"`,
			},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "view user via different user token",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"username":"test1"`,
			},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "view user via owner user token",
			Method: http.MethodGet,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"username":"test1"`,
			},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestUsersCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "everyone should be able to create a user",
			Method: http.MethodPost,
			URL:    "/api/collections/users/records",
			Body: strings.NewReader(`{
				"name":            "test_new",
				"email":           "test_new@example.com",
				"password":        "1234567890",
				"passwordConfirm": "1234567890"
			}`),
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"name":"test_new"`,
				`"verified":false`,
			},
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordCreateRequest":      1,
				"OnModelValidate":            1,
				"OnModelCreate":              1,
				"OnModelCreateExecute":       1,
				"OnModelAfterCreateSuccess":  1,
				"OnRecordEnrich":             1,
				"OnRecordValidate":           1,
				"OnRecordCreate":             1,
				"OnRecordCreateExecute":      1,
				"OnRecordAfterCreateSuccess": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestUsersUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "guest, try to update user",
			Method:          http.MethodPatch,
			URL:             "/api/collections/users/records/nwl39aj35c02p7l",
			Body:            strings.NewReader(`{"name":"test_update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as project link, try to update project owner",
			Method: http.MethodPatch,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user, try to update another user that shares a project ownership",
			Method: http.MethodPatch,
			URL:    "/api/collections/users/records/7rs5wkqeb5gggmn",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user, update own record",
			Method: http.MethodPatch,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"name":"test_update"`},
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordUpdateRequest":      1,
				"OnModelValidate":            1,
				"OnModelUpdate":              1,
				"OnModelUpdateExecute":       1,
				"OnModelAfterUpdateSuccess":  1,
				"OnRecordEnrich":             1,
				"OnRecordValidate":           1,
				"OnRecordUpdate":             1,
				"OnRecordUpdateExecute":      1,
				"OnRecordAfterUpdateSuccess": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestUsersDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "guest, try to delete user",
			Method:          http.MethodDelete,
			URL:             "/api/collections/users/records/nwl39aj35c02p7l",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as project link, try to delete project owner",
			Method: http.MethodDelete,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user, try to delete another user that shares a project ownership",
			Method: http.MethodDelete,
			URL:    "/api/collections/users/records/7rs5wkqeb5gggmn",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user, delete own record",
			Method: http.MethodDelete,
			URL:    "/api/collections/users/records/nwl39aj35c02p7l",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			BeforeTestFunc: func(t testing.TB, app *tests.TestApp, e *core.ServeEvent) {
				ownedProjects, err := app.FindRecordsByFilter("projects", "users.id ?= {:user}", "", 0, 0, dbx.Params{
					"user": "nwl39aj35c02p7l",
				})
				if err != nil || len(ownedProjects) == 0 {
					t.Fatalf("Failed to fetch owned projects: %v", err)
				}

				app.Store().Set("ownedProjects", ownedProjects)
			},
			AfterTestFunc: func(t testing.TB, app *tests.TestApp, res *http.Response) {
				ownedProjects, ok := app.Store().Get("ownedProjects").([]*core.Record)
				if !ok || len(ownedProjects) == 0 {
					t.Fatalf("Failed to retrieve previously fetched owned projects")
				}

				// ensure that only the single owner projects are deleted
				for _, p := range ownedProjects {
					_, err := app.FindRecordById("projects", p.Id)
					exists := err == nil

					expectToBeDeleted := len(p.GetStringSlice("users")) > 1
					if expectToBeDeleted != exists {
						t.Fatalf("Expected deleted state %v, got %v", expectToBeDeleted, exists)
					}
				}

				// all user related projects preferences should have been deleted
				prefs, err := app.FindRecordsByFilter("projectUserPreferences", "user ?= {:user}", "", 0, 0, dbx.Params{
					"user": "nwl39aj35c02p7l",
				})
				if err != nil || len(prefs) > 0 {
					t.Fatalf("Failed to fetch prefs or not all prefs are deleted - err:%v, total:%d", err, len(prefs))
				}
			},
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordDeleteRequest": 1,
				// include also the cascade rels operations
				"OnModelDelete":              26,
				"OnModelDeleteExecute":       26,
				"OnModelAfterDeleteSuccess":  26,
				"OnRecordDelete":             26,
				"OnRecordDeleteExecute":      26,
				"OnRecordAfterDeleteSuccess": 26,
				"OnModelUpdate":              2,
				"OnModelUpdateExecute":       2,
				"OnModelAfterUpdateSuccess":  2,
				"OnRecordUpdate":             2,
				"OnRecordUpdateExecute":      2,
				"OnRecordAfterUpdateSuccess": 2,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

// -------------------------------------------------------------------

func setupTestApp(t testing.TB) *tests.TestApp {
	testApp, err := tests.NewTestApp("./test_pb_data")
	if err != nil {
		t.Fatal(err)
	}

	bindAppHooks(testApp)

	return testApp
}

func getAuthToken(t testing.TB, collection, username string) string {
	app := setupTestApp(t)
	defer app.Cleanup()

	record, err := app.FindFirstRecordByData(collection, "username", username)
	if err != nil {
		t.Fatalf("Failed to find auth record with username %q: %v", username, err)
	}

	token, err := record.NewAuthToken()
	if err != nil {
		t.Fatalf("Failed to generate token for auth record with username %q: %v", username, err)
	}

	return token
}
