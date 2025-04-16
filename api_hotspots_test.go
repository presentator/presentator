package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestHotspotsList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/hotspots/records",
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
			URL:    "/api/collections/hotspots/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				// screen.prototype.project="t45bjlayvsx2yj0" || hotspotTemplate.prototype.project="t45bjlayvsx2yj0"
				`"totalItems":4`,
				`"id":"13tsk8uk9tc57n5"`,
				`"id":"s9yim63g4xaqm6g"`,
				`"id":"hn2rybm9nlpppta"`,
				`"id":"xeyr2230dez2oha"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       4,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				// screen.prototype="i7nda4y5rggo2eg" || hotspotTemplate.prototype="i7nda4y5rggo2eg"
				`"totalItems":3`,
				`"id":"nnnqtvmn4k1z7i5"`,
				`"id":"4kw6w7fick7pbqe"`,
				`"id":"t4yqp22lxhjtgg5"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       3,
			},
		},
		{
			Name:   "auth as user with no hotspots",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records",
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
			Name:   "auth as user with hotspots",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				// screen.prototype.project.users.username ?= "test1" || hotspotTemplate.prototype.project.users.username ?= "test1"
				`"totalItems":8`,
				`"id":"13tsk8uk9tc57n5"`,
				`"id":"s9yim63g4xaqm6g"`,
				`"id":"hn2rybm9nlpppta"`,
				`"id":"xeyr2230dez2oha"`,
				`"id":"27roujvmi90n3o6"`,
				`"id":"9vfl0pmh6yffy63"`,
				`"id":"qpk8csta069uqdb"`,
				`"id":"r1fufiv1x4w5qw7"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       8,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			URL:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"r1fufiv1x4w5qw7"`, `"hotspotTemplate":""`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"qpk8csta069uqdb"`, `"screen":""`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"nnnqtvmn4k1z7i5"`, `"hotspotTemplate":""`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedContent: []string{`"id":"4kw6w7fick7pbqe"`, `"screen":""`},
			ExpectedStatus:  200,
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"r1fufiv1x4w5qw7"`, `"hotspotTemplate":""`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodGet,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"xeyr2230dez2oha"`, `"screen":""`},
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

func TestHotspotsCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			URL:             "/api/collections/hotspots/records",
			Body:            strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with screen from the link project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with hotspotTemplate from the link project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user with screen from non-owned project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user with screen from owned project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"screen":"s02UKpYUSnYnh91"`,
				`"type":"back"`,
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
			Name:   "auth as user with hotspotTemplate from non-owned project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user with hotspotTemplate from owned project",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"hotspotTemplate":"fv1ua344vvpi0rj"`,
				`"type":"back"`,
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
			Name:   "auth as user with invalid settings format",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":[1,2,3]}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{"settings":{"code":"invalid_settings_data"`,
			},
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

func TestHotspotsUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			URL:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:            strings.NewReader(`{"width":100}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"r1fufiv1x4w5qw7"`,
				`"width":100`,
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
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"width":100}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"xeyr2230dez2oha"`,
				`"width":100`,
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
			Name:   "auth as user trying to change screen with non-owned",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"screen":"s0FSVfANCKVhmKF"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user trying to change hotspotTemplate with non-owned",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"hotspotTemplate":"auo00t8yt285q4a"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user trying to change hotspotTemplate with owned (no matter of the prototype)",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"screen":"s0TMdv2nbc4foMi","hotspotTemplate":"26m5by6c8w4kfk2"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"xeyr2230dez2oha"`,
				`"screen":"s0TMdv2nbc4foMi"`,
				`"hotspotTemplate":"26m5by6c8w4kfk2"`,
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
			Name:   "auth as user trying to clear both hotspot hotspotTemplate and screen fields",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"screen":"", "hotspotTemplate":""}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user trying to clear hotspotTemplate field",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"screen":"s0TMdv2nbc4foMi", "hotspotTemplate":""}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"xeyr2230dez2oha"`,
				`"screen":"s0TMdv2nbc4foMi"`,
				`"hotspotTemplate":""`,
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
			Name:   "auth as user trying to clear screen field",
			Method: http.MethodPatch,
			URL:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			Body:   strings.NewReader(`{"screen":"", "hotspotTemplate":"26m5by6c8w4kfk2"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"divglr2yoivqtr1"`,
				`"screen":""`,
				`"hotspotTemplate":"26m5by6c8w4kfk2"`,
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

func TestHotspotsDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			URL:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
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
		{
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodDelete,
			URL:    "/api/collections/hotspots/records/xeyr2230dez2oha",
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

func TestHotspotsNoteCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"note":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {
					"note": "` + strings.Repeat("a", 501) + `"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"note":{"code":"validation_length_out_of_range"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {
					"note": "` + strings.Repeat("a", 500) + `"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"note"`,
				`"settings":{"note":"aaa`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsScrollCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"scroll"`,
				`"scrollTop":0`,
				`"scrollLeft":0`,
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
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {
					"scrollTop": -1,
					"scrollLeft": -1
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{`,
				`"scrollLeft":{"code":"validation_min_greater_equal_than_required"`,
				`"scrollTop":{"code":"validation_min_greater_equal_than_required"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {
					"scrollTop": 5,
					"scrollLeft": 10
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"scroll"`,
				`"scrollTop":5`,
				`"scrollLeft":10`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsUrlCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"url":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {
					"url": "invalid"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"url":{"code":"validation_is_url"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {
					"url": "https://example.com"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"url"`,
				`"url":"https://example.com"`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsPrevCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"prev"`,
				`"settings":{"transition":""}`,
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
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {
					"transition": "invalid"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"prev"`,
				`"transition":"slide-left"`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsNextCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"next"`,
				`"settings":{"transition":""}`,
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
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {
					"transition": "invalid"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"next"`,
				`"transition":"slide-left"`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsBackCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"back"`,
				`"settings":{"transition":""}`,
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
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {
					"transition": "invalid"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"back"`,
				`"transition":"slide-left"`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsOverlayCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "overlay",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{`,
				`"overlayPosition":{"code":"validation_required"`,
				`"screen":{"code":"validation_required"`,
			},
			NotExpectedContent: []string{
				`"transition":`,
				`"fixOverlay":`,
				`"outsideClose":`,
				`"offsetTop":`,
				`"offsetBottom":`,
				`"offsetLeft":`,
				`"offsetRight":`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "overlay",
				"settings": {
					"screen":          "invalid",
					"transition":      "invalid",
					"overlayPosition": "invalid",
					"fixOverlay":      true,
					"outsideClose":    true,
					"offsetTop":       -1,
					"offsetBottom":    -1,
					"offsetLeft":      -1,
					"offsetRight":     -1
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{`,
				`"screen":{"code":"invalid_screen"`,
				`"transition":{"code":"validation_in_invalid"`,
				`"overlayPosition":{"code":"validation_in_invalid"`,
			},
			NotExpectedContent: []string{
				`"fixOverlay":`,
				`"outsideClose":`,
				`"offsetTop":`,
				`"offsetBottom":`,
				`"offsetLeft":`,
				`"offsetRight":`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "overlay",
				"settings": {
					"screen":          "s02UKpYUSnYnh91",
					"transition":      "fade",
					"overlayPosition": "top-left",
					"fixOverlay":      true,
					"outsideClose":    true,
					"offsetTop":       -1,
					"offsetBottom":    0,
					"offsetLeft":      10,
					"offsetRight":     100
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"overlay"`,
				`"screen":"s02UKpYUSnYnh91"`,
				`"transition":"fade"`,
				`"overlayPosition":"top-left"`,
				`"fixOverlay":true`,
				`"outsideClose":true`,
				`"offsetTop":-1`,
				`"offsetBottom":0`,
				`"offsetLeft":10`,
				`"offsetRight":100`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestHotspotsScreenCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:   "empty settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{`,
				`"screen":{"code":"validation_required"`,
			},
			NotExpectedContent: []string{
				`"transition":`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {
					"screen": "invalid",
					"transition": "invalid"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{`,
				`"screen":{"code":"invalid_screen"`,
				`"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			URL:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {
					"screen": "s02UKpYUSnYnh91",
					"transition": "slide-bottom"
				}
			}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"screen"`,
				`"screen":"s02UKpYUSnYnh91"`,
				`"transition":"slide-bottom"`,
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
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
