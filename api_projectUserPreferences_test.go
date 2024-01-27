package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestProjectUserPreferencesList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/projectUserPreferences/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with no rels",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with rels",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"0yv69huhsu35yev"`,
				`"id":"8pre7lvbupop4kp"`,
				`"id":"rsuqg09bov4i1ke"`,
				`"id":"sc7zhqg5s6tcv49"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectUserPreferencesView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodGet,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{
				`"id":"0yv69huhsu35yev"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

// only admins are allowed to create
func TestProjectUserPreferencesCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			Url:             "/api/collections/projectUserPreferences/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/projectUserPreferences/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user",
			Method: http.MethodPost,
			Url:    "/api/collections/projectUserPreferences/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestProjectUserPreferencesUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:            strings.NewReader(`{"favorite":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"0yv69huhsu35yev"`,
				`"favorite":true`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as rel-user but try to change the user field",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"user":"7rs5wkqeb5gggmn"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as rel-user but try to change the project field",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"project":"kk69rwtejro96iz"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as rel-user but try to change the lastVisited field",
			Method: http.MethodPatch,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"lastVisited":"2023-12-30"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

// only admins are allowed to delete
func TestProjectUserPreferencesDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodDelete,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodDelete,
			Url:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
