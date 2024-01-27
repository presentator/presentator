package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/labstack/echo/v5"
	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/models"
	"github.com/pocketbase/pocketbase/tests"
	"github.com/pocketbase/pocketbase/tokens"
)

func TestUsersList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "list users as guest",
			Method:          http.MethodGet,
			Url:             "/api/collections/users/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
		},
		{
			Name:   "list users via project link token",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":2`,
				`"username":"test2"`,
				`"username":"test1"`,
				`"test2@example.com"`, // public email
			},
			NotExpectedContent: []string{
				`"test1@example.com"`, // private email
			},
		},
		{
			Name:   "list users via user token",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
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
			Url:             "/api/collections/users/records/nwl39aj35c02p7l",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "view user via project link token (no project owner)",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records/0dwe3z1d444g9mo",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "view user via project link token (project owner)",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{
				`"username":"test1"`,
			},
		},
		{
			Name:   "view user via different user token",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{
				`"username":"test1"`,
			},
		},
		{
			Name:   "view user via owner user token",
			Method: http.MethodGet,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{
				`"username":"test1"`,
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
			Url:    "/api/collections/users/records",
			Body: strings.NewReader(`{
				"name":            "test_new",
				"email":           "test_new@example.com",
				"password":        "1234567890",
				"passwordConfirm": "1234567890"
			}`),
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
			ExpectedContent: []string{
				`"name":"test_new"`,
				`"verified":false`,
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
			Url:             "/api/collections/users/records/nwl39aj35c02p7l",
			Body:            strings.NewReader(`{"name":"test_update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as project link, try to update project owner",
			Method: http.MethodPatch,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user, try to update another user that shares a project ownership",
			Method: http.MethodPatch,
			Url:    "/api/collections/users/records/7rs5wkqeb5gggmn",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user, update own record",
			Method: http.MethodPatch,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			Body:   strings.NewReader(`{"name":"test_update"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
			ExpectedContent: []string{`"name":"test_update"`},
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
			Url:             "/api/collections/users/records/nwl39aj35c02p7l",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as project link, try to delete project owner",
			Method: http.MethodDelete,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user, try to delete another user that shares a project ownership",
			Method: http.MethodDelete,
			Url:    "/api/collections/users/records/7rs5wkqeb5gggmn",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user, delete own record",
			Method: http.MethodDelete,
			Url:    "/api/collections/users/records/nwl39aj35c02p7l",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			BeforeTestFunc: func(t *testing.T, app *tests.TestApp, e *echo.Echo) {
				ownedProjects, err := app.Dao().FindRecordsByFilter("projects", "users.id ?= {:user}", "", 0, 0, dbx.Params{
					"user": "nwl39aj35c02p7l",
				})
				if err != nil || len(ownedProjects) == 0 {
					t.Fatalf("Failed to fetch owned projects: %v", err)
				}

				app.Store().Set("ownedProjects", ownedProjects)
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				ownedProjects, ok := app.Store().Get("ownedProjects").([]*models.Record)
				if !ok || len(ownedProjects) == 0 {
					t.Fatalf("Failed to retrieve previously fetched owned projects")
				}

				// ensure that only the single owner projects are deleted
				for _, p := range ownedProjects {
					_, err := app.Dao().FindRecordById("projects", p.Id)
					exists := err == nil

					expectToBeDeleted := len(p.GetStringSlice("users")) > 1
					if expectToBeDeleted != exists {
						t.Fatalf("Expected deleted state %v, got %v", expectToBeDeleted, exists)
					}
				}

				// all user related projects preferences should have been deleted
				prefs, err := app.Dao().FindRecordsByFilter("projectUserPreferences", "user ?= {:user}", "", 0, 0, dbx.Params{
					"user": "nwl39aj35c02p7l",
				})
				if err != nil || len(prefs) > 0 {
					t.Fatalf("Failed to fetch prefs or not all prefs are deleted - err:%v, total:%d", err, len(prefs))
				}
			},
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				// include also the cascade rels operations
				"OnModelAfterDelete":  26,
				"OnModelBeforeDelete": 26,
				"OnModelAfterUpdate":  2,
				"OnModelBeforeUpdate": 2,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

// -------------------------------------------------------------------

func setupTestApp(t *testing.T) *tests.TestApp {
	testApp, err := tests.NewTestApp("./test_pb_data")
	if err != nil {
		t.Fatal(err)
	}

	bindAppHooks(testApp)

	return testApp
}

func getAuthToken(t *testing.T, collection, username string) string {
	app := setupTestApp(t)
	defer app.Cleanup()

	record, err := app.Dao().FindAuthRecordByUsername(collection, username)
	if err != nil {
		t.Fatalf("Failed to find auth record with username %q: %v", username, err)
	}

	token, err := tokens.NewRecordAuthToken(app, record)
	if err != nil {
		t.Fatalf("Failed to generate token for auth record with username %q: %v", username, err)
	}

	return token
}
