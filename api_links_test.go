package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestLinksList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/links/records",
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
			URL:    "/api/collections/links/records",
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
			Name:   "auth as user with no links",
			Method: http.MethodGet,
			URL:    "/api/collections/links/records",
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
			Name:   "auth as user with links",
			Method: http.MethodGet,
			URL:    "/api/collections/links/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"u4zqaq9b4stggps"`,
				`"id":"5kgzv7br5487j0d"`,
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

func TestLinksView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/links/records/5kgzv7br5487j0d",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for different project",
			Method: http.MethodGet,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the viewed project",
			Method: http.MethodGet,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"5kgzv7br5487j0d"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"5kgzv7br5487j0d"`},
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

func TestLinksCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			URL:             "/api/collections/links/records",
			Body:            strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			URL:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user nonexisting in the users list",
			Method: http.MethodPost,
			URL:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user existing in the users list",
			Method: http.MethodPost,
			URL:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"project":"t45bjlayvsx2yj0"`,
			},
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordCreateRequest":      1,
				"OnModelValidate":            1,
				"OnModelCreate":              1,
				"OnModelCreateExecute":       1,
				"OnModelAfterCreateSuccess":  1,
				"OnRecordValidate":           1,
				"OnRecordCreate":             1,
				"OnRecordCreateExecute":      1,
				"OnRecordAfterCreateSuccess": 1,
				"OnRecordEnrich":             1,
			},
		},
		{
			Name:   "auth as user but with onlyPrototypes from different project",
			Method: http.MethodPost,
			URL:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","onlyPrototypes":["urks33262s5eanb"],"password":"123456","passwordConfirm":"123456"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestLinksUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			URL:             "/api/collections/links/records/5kgzv7br5487j0d",
			Body:            strings.NewReader(`{"passwordProtect":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
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
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
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
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
				`"passwordProtect":true`,
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
			Name:   "auth as owner user changing password without requiring old one (manage rule check)",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"password":"new_pass","passwordConfirm":"new_pass"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
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
			Name:   "auth as owner but trying to change the project",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"project":["kk69rwtejro96iz"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as owner but with onlyPrototypes from different project",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"onlyPrototypes":["urks33262s5eanb"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as owner but with onlyPrototypes from the same project",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"onlyPrototypes":["rt8ee28zl1lbcr4"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
				`"onlyPrototypes":["rt8ee28zl1lbcr4"]`,
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
			Name:   "auth as owner but with reset onlyPrototypes",
			Method: http.MethodPatch,
			URL:    "/api/collections/links/records/u4zqaq9b4stggps",
			Body:   strings.NewReader(`{"onlyPrototypes":[]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"u4zqaq9b4stggps"`,
				`"onlyPrototypes":[]`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestLinksDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			URL:             "/api/collections/links/records/5kgzv7br5487j0d",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			URL:    "/api/collections/links/records/5kgzv7br5487j0d",
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
