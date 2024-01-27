package presentator

import (
	"net/http"
	"path"
	"strings"
	"testing"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/tests"
	"github.com/pocketbase/pocketbase/tools/list"
	"github.com/spf13/cast"
)

func TestPrototypesList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/prototypes/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":2`,
				`"id":"acovztxr3nfnz8e"`,
				`"id":"rt8ee28zl1lbcr4"`,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":1`,
				`"id":"i7nda4y5rggo2eg"`,
			},
		},
		{
			Name:   "auth as user with no prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"acovztxr3nfnz8e"`,
				`"id":"lrnr817ydrfdj80"`,
				`"id":"rt8ee28zl1lbcr4"`,
				`"id":"urks33262s5eanb"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestPrototypesView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/prototypes/records/acovztxr3nfnz8e",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with prototype from different project",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/bmtlodhqgt1h8og",
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
			Url:    "/api/collections/prototypes/records/i7nda4y5rggo2eg",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"i7nda4y5rggo2eg"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"acovztxr3nfnz8e"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"acovztxr3nfnz8e"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestPrototypesCreate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			Url:             "/api/collections/prototypes/records",
			Body:            strings.NewReader(`{"project":"t45bjlayvsx2yj0","title":"new"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/prototypes/records",
			Body:   strings.NewReader(`{"project":"t45bjlayvsx2yj0","title":"new"}`),
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
			Url:    "/api/collections/prototypes/records",
			Body:   strings.NewReader(`{"title":"new","project":"t45bjlayvsx2yj0"}`),
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
			Url:    "/api/collections/prototypes/records",
			Body:   strings.NewReader(`{"title":"new","project":"t45bjlayvsx2yj0"}`),
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
			Name:   "auth as user with invalid or empty data",
			Method: http.MethodPost,
			Url:    "/api/collections/prototypes/records",
			Body:   strings.NewReader(`{"title":"","project":""}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{`,
				`"title":{`,
				`"project":{`,
			},
		},
		{
			Name:   "auth as user owner with screens from different prototype",
			Method: http.MethodPost,
			Url:    "/api/collections/prototypes/records",
			Body:   strings.NewReader(`{"title":"new","project":"t45bjlayvsx2yj0","screensOrder":["s0l1ZFK0UAIhghd"]}`),
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

func TestPrototypesUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:            strings.NewReader(`{"title":"update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:   strings.NewReader(`{"title":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"acovztxr3nfnz8e"`,
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
			Name:   "auth as user owner trying to change the project",
			Method: http.MethodPatch,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:   strings.NewReader(`{"project":"kk69rwtejro96iz","title":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner trying to reset screensOrder",
			Method: http.MethodPatch,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:   strings.NewReader(`{"screensOrder":[]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"acovztxr3nfnz8e"`,
				`"screensOrder":[]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner trying to use screens from different prototype",
			Method: http.MethodPatch,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:   strings.NewReader(`{"screensOrder":["s0l1ZFK0UAIhghd"]}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner trying to reset the title",
			Method: http.MethodPatch,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			Body:   strings.NewReader(`{"title":""}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{`,
				`"title":{`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestPrototypesDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/prototypes/records/acovztxr3nfnz8e",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the prototype project",
			Method: http.MethodDelete,
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
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
			Url:    "/api/collections/prototypes/records/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				"OnModelAfterDelete":          15,
				"OnModelBeforeDelete":         15,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestPrototypesDuplicate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPost,
			Url:             "/api/pr/duplicate-prototype/acovztxr3nfnz8e",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  401,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the prototype project",
			Method: http.MethodPost,
			Url:    "/api/pr/duplicate-prototype/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPost,
			Url:    "/api/pr/duplicate-prototype/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  403,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodPost,
			Url:    "/api/pr/duplicate-prototype/acovztxr3nfnz8e",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"`,
				`"title":"Prototype 1 (copy)"`,
				`"project":"t45bjlayvsx2yj0"`,
				`"scale":1`,
				`"size":""`,
				`"screensOrder":["`,
			},
			NotExpectedContent: []string{
				`"id":"acovztxr3nfnz8e"`,
				`"screensOrder":[]`,
				`"screensOrder":["s02UKpYUSnYnh91","s0j0DfwURJenCpn","s01mGezNqrHL9uC"]`,
			},
			ExpectedEvents: map[string]int{
				"OnModelBeforeCreate": 10,
				"OnModelAfterCreate":  10,
				"OnModelBeforeUpdate": 1,
				"OnModelAfterUpdate":  1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				fsys, err := app.NewFilesystem()
				if err != nil {
					t.Fatalf("Failed to intialize the test app filesystem: %v", err)
				}
				defer fsys.Close()

				original, err := app.Dao().FindRecordById("prototypes", "acovztxr3nfnz8e")
				if err != nil {
					t.Fatalf("Failed to fetch original prototype: %v", err)
				}

				duplicated, err := app.Dao().FindFirstRecordByData("prototypes", "title", "Prototype 1 (copy)")
				if err != nil {
					t.Fatalf("Failed to fetch duplicated prototype: %v", err)
				}

				originalScreensOrder := original.GetStringSlice("screensOrder")
				duplicatedScreensOrder := duplicated.GetStringSlice("screensOrder")
				if len(originalScreensOrder) != len(duplicatedScreensOrder) {
					t.Fatalf("Expected %d screensOrder, got %d", len(originalScreensOrder), len(duplicatedScreensOrder))
				}

				// check duplicated screens
				// ---------------------------------------------------
				duplicatedScreens, err := app.Dao().FindRecordsByExpr("screens", dbx.HashExp{"prototype": duplicated.Id})
				if err != nil {
					t.Fatalf("Failed to fetch duplicated screens: %v", err)
				}

				if len(duplicatedScreens) != len(duplicatedScreensOrder) {
					t.Fatalf("Expected duplicated %d screens, got %d", len(duplicatedScreensOrder), len(duplicatedScreens))
				}

				for _, screen := range duplicatedScreens {
					if !list.ExistInSlice(screen.Id, duplicatedScreensOrder) {
						t.Fatalf("Expected duplicated screen %q to exist in %v", screen.Id, duplicatedScreensOrder)
					}

					// loosely check that the screen fields were cloned
					if screen.GetString("title") == "" {
						t.Fatalf("Expected duplicated screen %q title to be cloned", screen.Id)
					}

					// ensure that the screen file exists
					file := path.Join(screen.BaseFilesPath(), screen.GetString("file"))
					if ok, err := fsys.Exists(file); !ok {
						t.Fatalf("Expected duplicated screen file %q to exists: %v", file, err)
					}
				}
				// check duplicated hotspot templates
				// ---------------------------------------------------
				originalTemplates, err := app.Dao().FindRecordsByExpr("hotspotTemplates", dbx.HashExp{"prototype": original.Id})
				if err != nil {
					t.Fatalf("Failed to fetch original hotspot templates: %v", err)
				}

				duplicatedTemplates, err := app.Dao().FindRecordsByExpr("hotspotTemplates", dbx.HashExp{"prototype": duplicated.Id})
				if err != nil {
					t.Fatalf("Failed to fetch duplicated hotspot templates: %v", err)
				}

				if len(originalTemplates) != len(duplicatedTemplates) {
					t.Fatalf("Expected %d duplicated hotspot templates, got %d", len(originalTemplates), len(duplicatedTemplates))
				}

				originalTemplateIds := make([]string, 0, len(duplicatedTemplates))
				for _, template := range originalTemplates {
					originalTemplateIds = append(originalTemplateIds, template.Id)
				}

				duplicatedTemplateIds := make([]string, 0, len(duplicatedTemplates))
				for _, template := range duplicatedTemplates {
					if v := template.GetString("prototype"); v != duplicated.Id {
						t.Fatalf("Expected template.prototype %q, got %q", duplicated.Id, v)
					}

					duplicatedTemplateIds = append(duplicatedTemplateIds, template.Id)

					screenIds := template.GetStringSlice("screens")
					for _, id := range screenIds {
						if !list.ExistInSlice(id, duplicatedScreensOrder) {
							t.Fatalf("Failed to find template screen %q in %v", id, duplicatedScreensOrder)
						}
					}
				}

				// check duplicated hotspots
				// ---------------------------------------------------
				originalHotspots, err := app.Dao().FindRecordsByExpr("hotspots", dbx.Or(
					dbx.HashExp{"screen": list.ToInterfaceSlice(originalScreensOrder)},
					dbx.HashExp{"hotspotTemplate": list.ToInterfaceSlice(originalTemplateIds)},
				))
				if err != nil {
					t.Fatalf("Failed to fetch original hotspots: %v", err)
				}

				duplicatedHotspots, err := app.Dao().FindRecordsByExpr("hotspots", dbx.Or(
					dbx.HashExp{"screen": list.ToInterfaceSlice(duplicatedScreensOrder)},
					dbx.HashExp{"hotspotTemplate": list.ToInterfaceSlice(duplicatedTemplateIds)},
				))
				if err != nil {
					t.Fatalf("Failed to fetch duplicated hotspots: %v", err)
				}

				if len(originalHotspots) != len(duplicatedHotspots) {
					t.Fatalf("Expected %d duplicated hotspots, got %d", len(originalHotspots), len(duplicatedHotspots))
				}

				// ensure that the settings screen field is also updated (if exists)
				for _, hotspot := range duplicatedHotspots {
					settings := map[string]any{}
					if err := hotspot.UnmarshalJSONField("settings", &settings); err != nil {
						t.Fatalf("Failed to unmarshal duplicated hotspot settings: %v", err)
					}
					screen := cast.ToString(settings["screen"])
					if screen != "" && !list.ExistInSlice(screen, duplicatedScreensOrder) {
						t.Fatalf("Failed to find settings screen %q in %v", screen, duplicatedScreensOrder)
					}
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
