package presentator

import (
	"bytes"
	"image"
	"image/png"
	"io"
	"mime/multipart"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/pocketbase/pocketbase/tests"
)

func TestScreensList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/screens/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"s0l1ZFK0UAIhghd"`,
				`"id":"s0j0DfwURJenCpn"`,
				`"id":"s02UKpYUSnYnh91"`,
				`"id":"s01mGezNqrHL9uC"`,
			},
		},
		{
			Name:   "auth as link with restricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":1`,
				`"id":"s0FSVfANCKVhmKF"`,
			},
		},
		{
			Name:   "auth as user with no prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records",
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
			Url:    "/api/collections/screens/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":5`,
				`"id":"s0j0DfwURJenCpn"`,
				`"id":"s01mGezNqrHL9uC"`,
				`"id":"s02UKpYUSnYnh91"`,
				`"id":"s0l1ZFK0UAIhghd"`,
				`"id":"s0fIcb7gAuN4Aee"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestScreensView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with screen from different project",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records/s0FSVfANCKVhmKF",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with non-listed restricted prototype",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records/s0TMdv2nbc4foMi",
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
			Url:    "/api/collections/screens/records/s0FSVfANCKVhmKF",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s0FSVfANCKVhmKF"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s02UKpYUSnYnh91"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s02UKpYUSnYnh91"`},
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestScreensCreate(t *testing.T) {
	t.Parallel()

	// create a dummy img file
	img, err := os.OpenFile(filepath.Join(os.TempDir(), "pr_screen_test_image.png"), os.O_WRONLY|os.O_CREATE, 0644)
	if err != nil {
		t.Fatal(err)
	}
	imgRect := image.Rect(0, 0, 1, 1) // tiny 1x1 png
	if err := png.Encode(img, imgRect); err != nil {
		t.Fatalf("failed to save test png: %v", err)
	}
	img.Close()
	defer os.Remove(img.Name())

	// create a dummy txt file
	txt, err := os.OpenFile(filepath.Join(os.TempDir(), "pr_screen_text_file.txt"), os.O_WRONLY|os.O_CREATE, 0644)
	if err != nil {
		t.Fatal(err)
	}
	txt.Close()
	defer os.Remove(txt.Name())

	type testFormData struct {
		body *bytes.Buffer
		mw   *multipart.Writer
	}

	createFormData := func(filename, prototypeId, title string) *testFormData {
		body := new(bytes.Buffer)
		mw := multipart.NewWriter(body)
		defer mw.Close()

		mw.WriteField("title", title)
		mw.WriteField("prototype", prototypeId)

		f, err := os.Open(filename)
		if err != nil {
			t.Fatalf("Failed to open test file %v", err)
		}
		defer f.Close()

		// stub uploaded file
		w, err := mw.CreateFormFile("file", f.Name())
		if err != nil {
			t.Fatalf("Failed to create stub multipart file: %v", err)
		}
		if _, err := io.Copy(w, f); err != nil {
			t.Fatalf("Failed to copy multipart file content: %v", err)
		}

		return &testFormData{
			body: body,
			mw:   mw,
		}
	}

	validDatas := make([]*testFormData, 10)
	for i := 0; i < 10; i++ {
		validDatas[i] = createFormData(img.Name(), "acovztxr3nfnz8e", "new_screen")
	}

	invalidDatas := make([]*testFormData, 10)
	for i := 0; i < 10; i++ {
		invalidDatas[i] = createFormData(txt.Name(), "acovztxr3nfnz8e", "")
	}

	scenarios := []tests.ApiScenario{
		{
			Name:   "no auth",
			Method: http.MethodPost,
			Url:    "/api/collections/screens/records",
			Body:   validDatas[0].body,
			RequestHeaders: map[string]string{
				"Content-Type": validDatas[1].mw.FormDataContentType(),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			Url:    "/api/collections/screens/records",
			Body:   validDatas[1].body,
			RequestHeaders: map[string]string{
				"Content-Type":  validDatas[1].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPost,
			Url:    "/api/collections/screens/records",
			Body:   validDatas[2].body,
			RequestHeaders: map[string]string{
				"Content-Type":  validDatas[2].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodPost,
			Url:    "/api/collections/screens/records",
			Body:   validDatas[3].body,
			RequestHeaders: map[string]string{
				"Content-Type":  validDatas[3].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"title":"new_screen"`,
				`"prototype":"acovztxr3nfnz8e"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelAfterUpdate":          1,
				"OnModelBeforeCreate":         1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner with invalid data",
			Method: http.MethodPost,
			Url:    "/api/collections/screens/records",
			Body:   invalidDatas[0].body,
			RequestHeaders: map[string]string{
				"Content-Type":  invalidDatas[0].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{`,
				`"file":{"code":"validation_invalid_mime_type"`,
				`"title":{"code":"validation_required"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestScreensUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:            strings.NewReader(`{"title":"update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"title":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"s02UKpYUSnYnh91"`,
				`"title":"updated"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          2, // + project lastVitited update
				"OnModelBeforeUpdate":         2,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner trying to change the prototype to a new one from an owned project",
			Method: http.MethodPatch,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"prototype":"urks33262s5eanb"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"s02UKpYUSnYnh91"`,
				`"prototype":"urks33262s5eanb"`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          3, // + project lastVitited and old prototype screensOrder update
				"OnModelBeforeUpdate":         3,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user owner trying to change the prototype to a new one from a non-owned project",
			Method: http.MethodPatch,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"prototype":"i7nda4y5rggo2eg"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner with invalid data",
			Method: http.MethodPatch,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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

func TestScreensDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link for the screen project",
			Method: http.MethodDelete,
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			Url:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
				// note: includes cascade rels ipdate
				"OnModelAfterDelete":  5,
				"OnModelBeforeDelete": 5,
				"OnModelAfterUpdate":  3,
				"OnModelBeforeUpdate": 3,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
