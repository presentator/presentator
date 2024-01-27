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
			Url:             "/api/collections/hotspots/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				// screen.prototype.project="t45bjlayvsx2yj0" || hotspotTemplate.prototype.project="t45bjlayvsx2yj0"
				`"totalItems":4`,
				`"id":"13tsk8uk9tc57n5"`,
				`"id":"s9yim63g4xaqm6g"`,
				`"id":"hn2rybm9nlpppta"`,
				`"id":"xeyr2230dez2oha"`,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				// screen.prototype="i7nda4y5rggo2eg" || hotspotTemplate.prototype="i7nda4y5rggo2eg"
				`"totalItems":3`,
				`"id":"nnnqtvmn4k1z7i5"`,
				`"id":"4kw6w7fick7pbqe"`,
				`"id":"t4yqp22lxhjtgg5"`,
			},
		},
		{
			Name:   "auth as user with no hotspots",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with hotspots",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
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
			Url:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"r1fufiv1x4w5qw7"`, `"hotspotTemplate":""`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"qpk8csta069uqdb"`, `"screen":""`},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"nnnqtvmn4k1z7i5"`, `"hotspotTemplate":""`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"4kw6w7fick7pbqe"`, `"screen":""`},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"r1fufiv1x4w5qw7"`, `"hotspotTemplate":""`},
		},
		{
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodGet,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"xeyr2230dez2oha"`, `"screen":""`},
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
			Url:             "/api/collections/hotspots/records",
			Body:            strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with screen from the link project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with hotspotTemplate from the link project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with screen from non-owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with screen from owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"screen":"s02UKpYUSnYnh91"`,
				`"type":"back"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user with hotspotTemplate from non-owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with hotspotTemplate from owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":{}}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"hotspotTemplate":"fv1ua344vvpi0rj"`,
				`"type":"back"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user with invalid settings format",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body:   strings.NewReader(`{"hotspotTemplate":"fv1ua344vvpi0rj","type":"back","settings":[1,2,3]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{"settings":{"code":"invalid_settings_data"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
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
			Url:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:            strings.NewReader(`{"width":100}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"r1fufiv1x4w5qw7"`,
				`"width":100`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"width":100}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"xeyr2230dez2oha"`,
				`"width":100`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user trying to change screen with non-owned",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			Body:   strings.NewReader(`{"screen":"s0FSVfANCKVhmKF"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user trying to change hotspotTemplate with non-owned",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"hotspotTemplate":"auo00t8yt285q4a"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user trying to change hotspotTemplate with owned (no matter of the prototype)",
			Method: http.MethodPatch,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			Body:   strings.NewReader(`{"screen":"s0TMdv2nbc4foMi","hotspotTemplate":"26m5by6c8w4kfk2"}`),
			RequestHeaders: map[string]string{
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

func TestHotspotsDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via screen",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes via hotspotTemplate",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes trying to access hotspot from another project",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via screen",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/divglr2yoivqtr1",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from non-listed prototype via hotspotTemplate",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/qpk8csta069uqdb",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via screen",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/nnnqtvmn4k1z7i5",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes trying to access hotspot from listed prototype via hotspotTemplate",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/4kw6w7fick7pbqe",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner via screen",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner via hotstpotTemplate",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner via screen",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/r1fufiv1x4w5qw7",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnModelAfterDelete":          1,
				"OnModelBeforeDelete":         1,
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
			},
		},
		{
			Name:   "auth as user owner via hotstpotTemplate",
			Method: http.MethodDelete,
			Url:    "/api/collections/hotspots/records/xeyr2230dez2oha",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnModelAfterDelete":          1,
				"OnModelBeforeDelete":         1,
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"note":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {
					"note": "` + strings.Repeat("a", 501) + `"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"note":{"code":"validation_length_out_of_range"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "note",
				"settings": {
					"note": "` + strings.Repeat("a", 500) + `"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"note"`,
				`"settings":{"note":"aaa`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
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
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {
					"scrollTop": -1,
					"scrollLeft": -1
				}
			}`),
			RequestHeaders: map[string]string{
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
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "scroll",
				"settings": {
					"scrollTop": 5,
					"scrollLeft": 10
				}
			}`),
			RequestHeaders: map[string]string{
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
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"url":{"code":"validation_required"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {
					"url": "invalid"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"url":{"code":"validation_is_url"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "url",
				"settings": {
					"url": "https://example.com"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"url"`,
				`"url":"https://example.com"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"prev"`,
				`"settings":{"transition":""}`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {
					"transition": "invalid"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "prev",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"prev"`,
				`"transition":"slide-left"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"next"`,
				`"settings":{"transition":""}`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {
					"transition": "invalid"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "next",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"next"`,
				`"transition":"slide-left"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"back"`,
				`"settings":{"transition":""}`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {
					"transition": "invalid"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"settings":{"transition":{"code":"validation_in_invalid"`,
			},
			ExpectedEvents: map[string]int{
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "back",
				"settings": {
					"transition": "slide-left"
				}
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"type":"back"`,
				`"transition":"slide-left"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "overlay",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
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
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
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
			RequestHeaders: map[string]string{
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
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
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
			RequestHeaders: map[string]string{
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
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
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
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {}
			}`),
			RequestHeaders: map[string]string{
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
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "invalid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {
					"screen": "invalid",
					"transition": "invalid"
				}
			}`),
			RequestHeaders: map[string]string{
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
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "valid settings",
			Method: http.MethodPost,
			Url:    "/api/collections/hotspots/records",
			Body: strings.NewReader(`{
				"screen": "s02UKpYUSnYnh91",
				"type": "screen",
				"settings": {
					"screen": "s02UKpYUSnYnh91",
					"transition": "slide-bottom"
				}
			}`),
			RequestHeaders: map[string]string{
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
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
