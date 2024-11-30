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
			URL:             "/api/collections/projectUserPreferences/records",
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
			URL:    "/api/collections/projectUserPreferences/records",
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
			Name:   "auth as user with no rels",
			Method: http.MethodGet,
			URL:    "/api/collections/projectUserPreferences/records",
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
			Name:   "auth as user with rels",
			Method: http.MethodGet,
			URL:    "/api/collections/projectUserPreferences/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"0yv69huhsu35yev"`,
				`"id":"8pre7lvbupop4kp"`,
				`"id":"rsuqg09bov4i1ke"`,
				`"id":"sc7zhqg5s6tcv49"`,
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

func TestProjectUserPreferencesView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodGet,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodGet,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"0yv69huhsu35yev"`,
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

// only admins are allowed to create
func TestProjectUserPreferencesCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			URL:             "/api/collections/projectUserPreferences/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			URL:    "/api/collections/projectUserPreferences/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user",
			Method: http.MethodPost,
			URL:    "/api/collections/projectUserPreferences/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
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
			URL:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:            strings.NewReader(`{"favorite":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"favorite":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"0yv69huhsu35yev"`,
				`"favorite":true`,
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
		},
		{
			Name:   "auth as rel-user but try to change the user field",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"user":"7rs5wkqeb5gggmn"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel-user but try to change the project field",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"project":"kk69rwtejro96iz"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel-user but try to change the lastVisited field",
			Method: http.MethodPatch,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Body:   strings.NewReader(`{"lastVisited":"2023-12-30"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
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
			URL:             "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as non-rel user",
			Method: http.MethodDelete,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel-user",
			Method: http.MethodDelete,
			URL:    "/api/collections/projectUserPreferences/records/0yv69huhsu35yev",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
