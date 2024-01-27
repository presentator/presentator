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
			Url:             "/api/collections/links/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with no links",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with links",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"u4zqaq9b4stggps"`,
				`"id":"5kgzv7br5487j0d"`,
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
			Url:             "/api/collections/links/records/5kgzv7br5487j0d",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for different project",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the viewed project",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"5kgzv7br5487j0d"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"5kgzv7br5487j0d"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
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
			Url:             "/api/collections/links/records",
			Body:            strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
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
			Url:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
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
			Url:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","password":"123456","passwordConfirm":"123456"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"project":"t45bjlayvsx2yj0"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user but with onlyPrototypes from different project",
			Method: http.MethodPost,
			Url:    "/api/collections/links/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","onlyPrototypes":["urks33262s5eanb"],"password":"123456","passwordConfirm":"123456"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
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
			Url:             "/api/collections/links/records/5kgzv7br5487j0d",
			Body:            strings.NewReader(`{"passwordProtect":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
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
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
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
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"passwordProtect":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
				`"passwordProtect":true`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as owner user changing password without requiring old one (manage rule check)",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"password":"new_pass","passwordConfirm":"new_pass"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as owner but trying to change the project",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"project":["kk69rwtejro96iz"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as owner but with onlyPrototypes from different project",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"onlyPrototypes":["urks33262s5eanb"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as owner but with onlyPrototypes from the same project",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			Body:   strings.NewReader(`{"onlyPrototypes":["rt8ee28zl1lbcr4"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"5kgzv7br5487j0d"`,
				`"onlyPrototypes":["rt8ee28zl1lbcr4"]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as owner but with reset onlyPrototypes",
			Method: http.MethodPatch,
			Url:    "/api/collections/links/records/u4zqaq9b4stggps",
			Body:   strings.NewReader(`{"onlyPrototypes":[]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"u4zqaq9b4stggps"`,
				`"onlyPrototypes":[]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
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
			Url:             "/api/collections/links/records/5kgzv7br5487j0d",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodDelete,
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
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
			Url:    "/api/collections/links/records/5kgzv7br5487j0d",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				"OnModelAfterDelete":          1,
				"OnModelBeforeDelete":         1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
