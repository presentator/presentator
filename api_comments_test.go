package presentator

import (
	"net/http"
	"strings"
	"testing"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/tests"
	"github.com/pocketbase/pocketbase/tools/list"
)

func TestCommentsList(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/comments/records",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":3`,
				`"id":"k5sz9w1q9pyh6i4"`,
				`"id":"6qv018x53bs70wc"`,
				`"id":"ns7cstm4gt7ueeh"`,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes but without allowed comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as link with restricted prototypes and allowed comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":4`,
				`"id":"73z18h3t1j19s2n"`,
				`"id":"3l3j15w9qd8ylou"`,
				`"id":"l1444cdhjs6tv8d"`,
				`"id":"j33dqz1alxtyw2j"`,
			},
		},
		{
			Name:   "auth as user with no projects with comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{`"items":[]`},
		},
		{
			Name:   "auth as user with project with comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedEvents: map[string]int{"OnRecordsListRequest": 1},
			ExpectedContent: []string{
				`"totalItems":9`,
				`"id":"ns7cstm4gt7ueeh"`,
				`"id":"73z18h3t1j19s2n"`,
				`"id":"tulqscil5ewos75"`,
				`"id":"2b8qam0oofz3lb6"`,
				`"id":"k5sz9w1q9pyh6i4"`,
				`"id":"6qv018x53bs70wc"`,
				`"id":"3l3j15w9qd8ylou"`,
				`"id":"l1444cdhjs6tv8d"`,
				`"id":"j33dqz1alxtyw2j"`,
				`"guestEmail":"test_guest@example.com"`,
				`"guestEmail":"test_guest2@example.com"`,
				// ensures that there is one comment from another user from a shared project
				`"user":"nwl39aj35c02p7l"`,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestCommentsView(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodGet,
			Url:             "/api/collections/comments/records/6qv018x53bs70wc",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"6qv018x53bs70wc"`},
		},
		{
			Name:   "auth as link with unrestricted prototypes but without allowed comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records/xw9h2ulsd4fs4rd",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and allowed comments",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records/73z18h3t1j19s2n",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"73z18h3t1j19s2n"`},
		},
		{
			Name:   "auth as user without access to the comment's project",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records/2b8qam0oofz3lb6",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with access to the comment's project",
			Method: http.MethodGet,
			Url:    "/api/collections/comments/records/2b8qam0oofz3lb6",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedEvents:  map[string]int{"OnRecordViewRequest": 1},
			ExpectedContent: []string{`"id":"2b8qam0oofz3lb6"`},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestCommentsCreate(t *testing.T) {
	t.Parallel()

	checkNotifications := func(t *testing.T, app *tests.TestApp, commentMsg string, usernames ...string) {
		comment, err := app.Dao().FindFirstRecordByData("comments", "message", commentMsg)
		if err != nil {
			t.Fatalf("Failed to fetch the created comment: %v", err)
		}

		users, err := app.Dao().FindRecordsByExpr("users", dbx.In("username", list.ToInterfaceSlice(usernames)...))
		if err != nil || len(users) == 0 {
			t.Fatalf("Failed to fetch notifications users: %v", err)
		}

		// check for notifications
		notifications, err := app.Dao().FindRecordsByExpr("notifications", dbx.HashExp{"comment": comment.Id})
		if err != nil || len(notifications) == 0 {
			t.Fatalf("Failed to fetch notifications: %v", err)
		}

		for len(notifications) != len(users) {
			t.Fatalf("Expected %d notifications, got %d", len(users), len(notifications))
		}

	UsersLoop:
		for _, u := range users {
			for _, n := range notifications {
				if n.GetString("user") == u.Id {
					continue UsersLoop
				}
			}
			t.Fatalf("Failed to find notification for user %s(%s)", u.Id, u.Username())
		}
	}

	scenarios := []tests.ApiScenario{
		{
			Name:   "no auth",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0j0DfwURJenCpn",
				"message":    "new_created",
				"guestEmail": "test_guest@example.com"
			}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},

		// link auth checks
		// -----------------------------------------------------------
		{
			Name:   "auth as link with unrestricted prototypes to a screen from different project",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":  "s0fIcb7gAuN4Aee",
				"message": "test_new",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes attempting to impersonate a user",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":  "s0j0DfwURJenCpn",
				"message": "test_new",
				"user":    "nwl39aj35c02p7l"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes with guestEmail matching with one of the project owners",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0j0DfwURJenCpn",
				"message":    "test_new",
				"guestEmail": "test1@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes with valid data",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0j0DfwURJenCpn",
				"message":    "new_created",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"message":"new_created"`,
				`"guestEmail":"test_guest@example.com"`,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				checkNotifications(t, app, "new_created", "test1", "test2") // mixed allowEmailNotifications checks
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          3, // notifications
				"OnModelBeforeCreate":         3,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes but without allowed comments",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0j0DfwURJenCpn",
				"message":    "new_created",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and screen from NON-listed prototype and allowed comments",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0TMdv2nbc4foMi",
				"message":    "new_created",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and screen from listed prototype and allowed comments",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s0FSVfANCKVhmKF",
				"message":    "new_created",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"message":"new_created"`,
				`"guestEmail":"test_guest@example.com"`,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				checkNotifications(t, app, "new_created", "test2")
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          2, // notifications
				"OnModelBeforeCreate":         2,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},

		// user auth checks
		// -----------------------------------------------------------
		{
			Name:   "auth as user with comment screen from non owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":  "s0fIcb7gAuN4Aee",
				"message": "new_created",
				"user":    "7rs5wkqeb5gggmn"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with comment screen from owned project",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":  "s02UKpYUSnYnh91",
				"message": "new_created",
				"user":    "7rs5wkqeb5gggmn"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"screen":"s02UKpYUSnYnh91"`,
				`"message":"new_created"`,
				`"user":"7rs5wkqeb5gggmn"`,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				checkNotifications(t, app, "new_created", "test1")
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          2, // notifications
				"OnModelBeforeCreate":         2,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
		},
		{
			Name:   "auth as user but cannot create comment on behalf another user",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":  "s02UKpYUSnYnh91",
				"message": "new_created",
				"user":    "nwl39aj35c02p7l"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "guestEmail cannot be set together with the user field",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"screen":     "s02UKpYUSnYnh91",
				"message":    "new_created",
				"user":       "7rs5wkqeb5gggmn",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "not allowed to reply to comment from a different screen",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"replyTo": "6qv018x53bs70wc",
				"screen":  "s02UKpYUSnYnh91",
				"message": "new_created",
				"user":    "7rs5wkqeb5gggmn"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  400,
			ExpectedContent: []string{`"data":{}`},
		},

		// guest sends
		// -----------------------------------------------------------
		{
			Name:   "send a reply as user to trigger guest emails",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"replyTo": "3l3j15w9qd8ylou",
				"screen":  "s0FSVfANCKVhmKF",
				"message": "test_reply",
				"user":    "7rs5wkqeb5gggmn"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"message":"test_reply"`},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          1,
				"OnModelBeforeCreate":         1,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				expectedAddrs := []string{
					"test_guest@example.com",
					"test_guest2@example.com",
				}

				mails := app.TestMailer.SentMessages
				if len(mails) != len(expectedAddrs) {
					t.Fatalf("Expected %d emails, got %d", len(expectedAddrs), len(mails))
				}

				for _, addr := range expectedAddrs {
					var exists bool
					for _, m := range mails {
						if !strings.Contains(m.HTML, "test_reply") {
							t.Fatalf("Expected to find 'test_reply' in\n%v", m.HTML)
						}

						if m.To[0].Address == addr {
							exists = true
							break
						}
					}
					if !exists {
						t.Fatalf("Missing mail to %q", addr)
					}
				}
			},
		},
		{
			Name:   "send a reply as guest to test whether the guest author will be excluded",
			Method: http.MethodPost,
			Url:    "/api/collections/comments/records",
			Body: strings.NewReader(`{
				"replyTo":    "3l3j15w9qd8ylou",
				"screen":     "s0FSVfANCKVhmKF",
				"message":    "test_reply",
				"guestEmail": "test_guest@example.com"
			}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  200,
			ExpectedContent: []string{`"message":"test_reply"`},
			ExpectedEvents: map[string]int{
				"OnModelAfterCreate":          2, // notifications
				"OnModelBeforeCreate":         2,
				"OnRecordAfterCreateRequest":  1,
				"OnRecordBeforeCreateRequest": 1,
			},
			AfterTestFunc: func(t *testing.T, app *tests.TestApp, res *http.Response) {
				checkNotifications(t, app, "test_reply", "test2")

				expectedAddrs := []string{
					"test_guest2@example.com",
				}

				mails := app.TestMailer.SentMessages
				if len(mails) != len(expectedAddrs) {
					t.Fatalf("Expected %d emails, got %d", len(expectedAddrs), len(mails))
				}

				for _, addr := range expectedAddrs {
					var exists bool
					for _, m := range mails {
						if !strings.Contains(m.HTML, "test_reply") {
							t.Fatalf("Expected to find 'test_reply' in\n%v", m.HTML)
						}

						if m.To[0].Address == addr {
							exists = true
							break
						}
					}
					if !exists {
						t.Fatalf("Missing mail to %q", addr)
					}
				}
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}

func TestCommentsUpdate(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodPatch,
			Url:             "/api/collections/comments/records/6qv018x53bs70wc",
			Body:            strings.NewReader(`{"resolved":true}`),
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},

		// link auth checks
		// -----------------------------------------------------------
		{
			Name:   "auth as link try to change left",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"left":1}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link try to change top",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"top":1}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link try to change message",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"message":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link try to change guestEmail",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"guestEmail":"new_guest@example.com"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link try to change user",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"user":"nwl39aj35c02p7l"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change replyTo",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"replyTo":"k5sz9w1q9pyh6i4"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link try to change resolved",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"resolved":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"6qv018x53bs70wc"`,
				`"resolved":true`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as link with unrestricted prototypes but without allowed comments",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/94obnp094aqthp5",
			Body:   strings.NewReader(`{"resolved":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and comment from NON-listed prototype and allowed comments",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/2b8qam0oofz3lb6",
			Body:   strings.NewReader(`{"resolved":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and screen from listed prototype and allowed comments",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/l1444cdhjs6tv8d",
			Body:   strings.NewReader(`{"resolved":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"l1444cdhjs6tv8d"`,
				`"resolved":true`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},

		// user auth checks
		// -----------------------------------------------------------
		{
			Name:   "auth as user try to change left",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"left":1}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"6qv018x53bs70wc"`,
				`"left":1`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user try to change top",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"top":1}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"6qv018x53bs70wc"`,
				`"top":1`,
			},
			ExpectedEvents: map[string]int{
				"OnModelAfterUpdate":          1,
				"OnModelBeforeUpdate":         1,
				"OnRecordAfterUpdateRequest":  1,
				"OnRecordBeforeUpdateRequest": 1,
			},
		},
		{
			Name:   "auth as user try to change message",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"message":"updated"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change guestEmail",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"guestEmail":"new_guest@example.com"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change the comment user",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"user":"7rs5wkqeb5gggmn"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change screen",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"screen":"s02UKpYUSnYnh91"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change replyTo",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"replyTo":"k5sz9w1q9pyh6i4"}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user try to change resolved",
			Method: http.MethodPatch,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			Body:   strings.NewReader(`{"resolved":true}`),
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test1"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 200,
			ExpectedContent: []string{
				`"id":"6qv018x53bs70wc"`,
				`"resolved":true`,
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

func TestCommentsDelete(t *testing.T) {
	t.Parallel()

	scenarios := []tests.ApiScenario{
		{
			Name:            "no auth",
			Method:          http.MethodDelete,
			Url:             "/api/collections/comments/records/6qv018x53bs70wc",
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes",
			Method: http.MethodDelete,
			Url:    "/api/collections/comments/records/6qv018x53bs70wc",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test1"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with unrestricted prototypes but without allowed comments",
			Method: http.MethodDelete,
			Url:    "/api/collections/comments/records/xw9h2ulsd4fs4rd",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test2"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as link with restricted prototypes and allowed comments",
			Method: http.MethodDelete,
			Url:    "/api/collections/comments/records/73z18h3t1j19s2n",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "links", "test3"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user without access to the comment's project",
			Method: http.MethodDelete,
			Url:    "/api/collections/comments/records/2b8qam0oofz3lb6",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test4"),
			},
			TestAppFactory:  setupTestApp,
			ExpectedStatus:  404,
			ExpectedContent: []string{`"data":{}`},
		},
		{
			Name:   "auth as user with access to the comment's project",
			Method: http.MethodDelete,
			Url:    "/api/collections/comments/records/2b8qam0oofz3lb6",
			RequestHeaders: map[string]string{
				"Authorization": getAuthToken(t, "users", "test2"),
			},
			TestAppFactory: setupTestApp,
			ExpectedStatus: 204,
			ExpectedEvents: map[string]int{
				"OnModelAfterDelete":          2, // +1 for the reply
				"OnModelBeforeDelete":         2,
				"OnRecordAfterDeleteRequest":  1,
				"OnRecordBeforeDeleteRequest": 1,
			},
		},
	}

	for _, scenario := range scenarios {
		scenario.Test(t)
	}
}
