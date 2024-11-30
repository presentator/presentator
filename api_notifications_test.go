package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestNotificationsList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/notifications/records",
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
			URL:    "/api/collections/notifications/records",
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
			Name:   "auth as user with no notifications",
			Method: http.MethodGet,
			URL:    "/api/collections/notifications/records",
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
			Name:   "auth as user with notifications",
			Method: http.MethodGet,
			URL:    "/api/collections/notifications/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"13ricc0qvbpk7aj"`,
				`"id":"yv8xu4zpoynuin1"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       2,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestNotificationsView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/notifications/records/yv8xu4zpoynuin1",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
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
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel user",
			Method: http.MethodGet,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"yv8xu4zpoynuin1"`},
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
func TestNotificationsCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			URL:             "/api/collections/notifications/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			URL:    "/api/collections/notifications/records",
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
			URL:    "/api/collections/notifications/records",
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

func TestNotificationsUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			URL:             "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Body:            strings.NewReader(`{"read":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Body:   strings.NewReader(`{"read":true}`),
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
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Body:   strings.NewReader(`{"read":true}`),
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
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Body:   strings.NewReader(`{"read":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"yv8xu4zpoynuin1"`,
				`"read":true`,
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
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
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
			Name:   "auth as rel-user but try to change the comment field",
			Method: http.MethodPatch,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Body:   strings.NewReader(`{"comment":"3l3j15w9qd8ylou"}`),
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

func TestNotificationsDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			URL:             "/api/collections/notifications/records/yv8xu4zpoynuin1",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
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
			Method: http.MethodDelete,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as rel user",
			Method: http.MethodDelete,
			URL:    "/api/collections/notifications/records/yv8xu4zpoynuin1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordDeleteRequest":      1,
				"OnModelDelete":              1,
				"OnModelDeleteExecute":       1,
				"OnModelAfterDeleteSuccess":  1,
				"OnRecordDelete":             1,
				"OnRecordDeleteExecute":      1,
				"OnRecordAfterDeleteSuccess": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
