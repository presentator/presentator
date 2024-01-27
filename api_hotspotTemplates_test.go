package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestHotspotTemplatesList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/hotspotTemplates/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"ftiv5oj2w7fr6yh"`,
				`"id":"fv1ua344vvpi0rj"`,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"dc7gecccy4l5sbc"`,
				`"id":"auo00t8yt285q4a"`,
			},
		},
		{
			Name:   "auth as user with no hotspot templates",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with hotspot templates",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"ftiv5oj2w7fr6yh"`,
				`"id":"fv1ua344vvpi0rj"`,
				`"id":"jedv62zyewbfh5i"`,
				`"id":"x9cksozy60sgg2a"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotTemplatesView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with prototype from different project",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with non-listed restricted prototype",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records/26m5by6c8w4kfk2",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with listed restricted prototype",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records/auo00t8yt285q4a",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"auo00t8yt285q4a"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"fv1ua344vvpi0rj"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
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
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"fv1ua344vvpi0rj"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotTemplatesCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			Url:             "/api/collections/hotspotTemplates/records",
			Body:            strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"title":"new"`},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user with empty data",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"","title":""}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{`,
				`"title":{`,
				`"prototype":{`,
			},
		},
		{
			Name:   "auth as user with screens from different prototype",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"title":"new","prototype":"rt8ee28zl1lbcr4","screens":["s01mGezNqrHL9uC"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
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

func TestHotspotTemplatesUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:            strings.NewReader(`{"title":"update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"update"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"update"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"fv1ua344vvpi0rj"`,
				`"title":"updated"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner trying to reset the screens",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"screens":[]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"fv1ua344vvpi0rj"`,
				`"screens":[]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner trying to change the prototype",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner trying to use screens from diffrent prototype",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"screens":["s0l1ZFK0UAIhghd"]}`),
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

func TestHotspotTemplatesDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the prototype project",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				"OnModelAfterDelete":          2,
				"OnModelBeforeDelete":         2,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
