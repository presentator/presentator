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
			URL:             "/api/collections/screens/records",
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
			URL:    "/api/collections/screens/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"s0l1ZFK0UAIhghd"`,
				`"id":"s0j0DfwURJenCpn"`,
				`"id":"s02UKpYUSnYnh91"`,
				`"id":"s01mGezNqrHL9uC"`,
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
			URL:    "/api/collections/screens/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":1`,
				`"id":"s0FSVfANCKVhmKF"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       1,
			},
		},
		{
			Name:   "auth as user with no prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records",
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
			Name:   "auth as user with prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"totalItems":5`,
				`"id":"s0j0DfwURJenCpn"`,
				`"id":"s01mGezNqrHL9uC"`,
				`"id":"s02UKpYUSnYnh91"`,
				`"id":"s0l1ZFK0UAIhghd"`,
				`"id":"s0fIcb7gAuN4Aee"`,
			},
			ExpectedEvents: map[string]int{
				"*":                    0,
				"OnRecordsListRequest": 1,
				"OnRecordEnrich":       5,
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
			URL:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with screen from different project",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records/s0FSVfANCKVhmKF",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link with non-listed restricted prototype",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records/s0TMdv2nbc4foMi",
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
			URL:    "/api/collections/screens/records/s0FSVfANCKVhmKF",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s0FSVfANCKVhmKF"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s02UKpYUSnYnh91"`},
			ExpectedEvents: map[string]int{
				"*":                   0,
				"OnRecordViewRequest": 1,
				"OnRecordEnrich":      1,
			},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodGet,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"id":"s02UKpYUSnYnh91"`},
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
		validDatas[i] = createFormData(img.Name(), "acovztxr3nfnz8e", "   new_screen   ")
	}

	invalidDatas := make([]*testFormData, 10)
	for i := 0; i < 10; i++ {
		invalidDatas[i] = createFormData(txt.Name(), "acovztxr3nfnz8e", "")
	}

	scenarios := []tests.ApiScenario{
		{
			Name:   "no auth",
			Method: http.MethodPost,
			URL:    "/api/collections/screens/records",
			Body:   validDatas[0].body,
			Headers: map[string]string{
				"Content-Type": validDatas[0].mw.FormDataContentType(),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPost,
			URL:    "/api/collections/screens/records",
			Body:   validDatas[1].body,
			Headers: map[string]string{
				"Content-Type":  validDatas[1].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user non-owner",
			Method: http.MethodPost,
			URL:    "/api/collections/screens/records",
			Body:   validDatas[2].body,
			Headers: map[string]string{
				"Content-Type":  validDatas[2].mw.FormDataContentType(),
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner",
			Method: http.MethodPost,
			URL:    "/api/collections/screens/records",
			Body:   validDatas[3].body,
			Headers: map[string]string{
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
				"*":                          0,
				"OnRecordCreateRequest":      1,
				"OnModelValidate":            2, // screen + prototype order
				"OnModelCreate":              1,
				"OnModelCreateExecute":       1,
				"OnModelAfterCreateSuccess":  1,
				"OnModelUpdate":              1,
				"OnModelUpdateExecute":       1,
				"OnModelAfterUpdateSuccess":  1,
				"OnRecordValidate":           2,
				"OnRecordCreate":             1,
				"OnRecordCreateExecute":      1,
				"OnRecordAfterCreateSuccess": 1,
				"OnRecordUpdate":             1,
				"OnRecordUpdateExecute":      1,
				"OnRecordAfterUpdateSuccess": 1,
				"OnRecordEnrich":             1,
			},
		},
		{
			Name:   "auth as user owner with invalid data",
			Method: http.MethodPost,
			URL:    "/api/collections/screens/records",
			Body:   invalidDatas[0].body,
			Headers: map[string]string{
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
			ExpectedEvents: map[string]int{
				"*":                        0,
				"OnRecordCreateRequest":    1,
				"OnModelCreate":            1,
				"OnModelValidate":          1,
				"OnModelAfterCreateError":  1,
				"OnRecordCreate":           1,
				"OnRecordValidate":         1,
				"OnRecordAfterCreateError": 1,
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
			URL:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:            strings.NewReader(`{"title":"update"}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link",
			Method: http.MethodPatch,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"title":"   updated   "}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"s02UKpYUSnYnh91"`,
				`"title":"updated"`,
			},
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordUpdateRequest":      1,
				"OnModelValidate":            2, // + project lastVitited update
				"OnModelUpdate":              2,
				"OnModelUpdateExecute":       2,
				"OnModelAfterUpdateSuccess":  2,
				"OnRecordValidate":           2,
				"OnRecordUpdate":             2,
				"OnRecordUpdateExecute":      2,
				"OnRecordAfterUpdateSuccess": 2,
				"OnRecordEnrich":             1,
			},
		},
		{
			Name:   "auth as user owner trying to change the prototype to a new one from an owned project",
			Method: http.MethodPatch,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"prototype":"urks33262s5eanb"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"s02UKpYUSnYnh91"`,
				`"prototype":"urks33262s5eanb"`,
			},
			ExpectedEvents: map[string]int{
				"*":                          0,
				"OnRecordUpdateRequest":      1,
				"OnModelValidate":            3, // + project lastVitited and old prototype screensOrder update
				"OnModelUpdate":              3,
				"OnModelUpdateExecute":       3,
				"OnModelAfterUpdateSuccess":  3,
				"OnRecordValidate":           3,
				"OnRecordUpdate":             3,
				"OnRecordUpdateExecute":      3,
				"OnRecordAfterUpdateSuccess": 3,
				"OnRecordEnrich":             1,
			},
		},
		{
			Name:   "auth as user owner trying to change the prototype to a new one from a non-owned project",
			Method: http.MethodPatch,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"prototype":"i7nda4y5rggo2eg"}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as user owner with invalid data",
			Method: http.MethodPatch,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Body:   strings.NewReader(`{"title":""}`),
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 400,
			ExpectedContent: []string{
				`"data":{`,
				`"title":{`,
			},
			ExpectedEvents: map[string]int{
				"*":                        0,
				"OnRecordUpdateRequest":    1,
				"OnModelUpdate":            1,
				"OnModelValidate":          1,
				"OnModelAfterUpdateError":  1,
				"OnRecordUpdate":           1,
				"OnRecordValidate":         1,
				"OnRecordAfterUpdateError": 1,
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
			URL:             "/api/collections/screens/records/s02UKpYUSnYnh91",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
			ExpectedEvents:  map[string]int{"*": 0},
		},
		{
			Name:   "auth as link for the screen project",
			Method: http.MethodDelete,
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
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
			URL:    "/api/collections/screens/records/s02UKpYUSnYnh91",
			Headers: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"*":                     0,
				"OnRecordDeleteRequest": 1,
				// note: includes cascade rels ipdate
				"OnModelDelete":              5,
				"OnModelDeleteExecute":       5,
				"OnModelAfterDeleteSuccess":  5,
				"OnModelUpdate":              3,
				"OnModelUpdateExecute":       3,
				"OnModelAfterUpdateSuccess":  3,
				"OnRecordDelete":             5,
				"OnRecordDeleteExecute":      5,
				"OnRecordAfterDeleteSuccess": 5,
				"OnRecordUpdate":             3,
				"OnRecordUpdateExecute":      3,
				"OnRecordAfterUpdateSuccess": 3,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
