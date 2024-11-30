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
			URL:             "/api/collections/hotspotTemplates/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"items":[]`},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"ftiv5oj2w7fr6yh"`,
				`"id":"fv1ua344vvpi0rj"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       2,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"dc7gecccy4l5sbc"`,
				`"id":"auo00t8yt285q4a"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       2,
			},
		},
		{
			Name:   "auth as user with no hotspot templates",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records",
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
			Name:   "auth as user with hotspot templates",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"ftiv5oj2w7fr6yh"`,
				`"id":"fv1ua344vvpi0rj"`,
				`"id":"jedv62zyewbfh5i"`,
				`"id":"x9cksozy60sgg2a"`,
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

func TestHotspotTemplatesView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with prototype from different project",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with non-listed restricted prototype",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records/26m5by6c8w4kfk2",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with listed restricted prototype",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records/auo00t8yt285q4a",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"auo00t8yt285q4a"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"fv1ua344vvpi0rj"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
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
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"fv1ua344vvpi0rj"`},
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

func TestHotspotTemplatesCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			URL:             "/api/collections/hotspotTemplates/records",
			Body:            strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
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
			URL:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
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
			Name:   "auth as user non-owner",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
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
			Name:   "auth as user owner",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4","title":"new"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"title":"new"`},
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
			Name:   "auth as user with empty data",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"prototype":"","title":""}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{}`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user with screens from different prototype",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspotTemplates/records",
			Body:   strings.NewReader(`{"title":"new","prototype":"rt8ee28zl1lbcr4","screens":["s01mGezNqrHL9uC"]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
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

func TestHotspotTemplatesUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			URL:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:            strings.NewReader(`{"title":"update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"update"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"update"}`),
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
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"title":"updated"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"fv1ua344vvpi0rj"`,
				`"title":"updated"`,
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
			Name:   "auth as user owner trying to reset the screens",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"screens":[]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"fv1ua344vvpi0rj"`,
				`"screens":[]`,
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
			Name:   "auth as user owner trying to change the prototype",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"prototype":"rt8ee28zl1lbcr4"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner trying to use screens from diffrent prototype",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Body:   strings.NewReader(`{"screens":["s0l1ZFK0UAIhghd"]}`),
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

func TestHotspotTemplatesDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			URL:             "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link for the prototype project",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
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
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspotTemplates/records/fv1ua344vvpi0rj",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordDeleteRequest":      1,
				"OnModelDelete":              2,
				"OnModelDeleteExecute":       2,
				"OnModelAfterDeleteSuccess":  2,
				"OnRecordDelete":             2,
				"OnRecordDeleteExecute":      2,
				"OnRecordAfterDeleteSuccess": 2,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
