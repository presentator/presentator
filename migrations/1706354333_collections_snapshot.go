package migrations

import (
	"encoding/json"

	"github.com/pocketbase/dbx"
	"github.com/pocketbase/pocketbase/daos"
	m "github.com/pocketbase/pocketbase/migrations"
	"github.com/pocketbase/pocketbase/models"
)

func init() {
	m.Register(func(db dbx.Builder) error {
		jsonData := `[
			{
				"id": "rcazu94eb6tpgkz",
				"created": "2023-04-28 21:34:50.405Z",
				"updated": "2024-01-12 20:36:48.286Z",
				"name": "projects",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "nlxyjnxl",
						"name": "title",
						"type": "text",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "b7esggok",
						"name": "users",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "_pb_users_auth_",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": null,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "juozzdkb",
						"name": "archived",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_K2anj3K` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `archived` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_u6OgtVh` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_0rVVvqv` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `updated` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  id = @request.auth.project ||\n  users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"updateRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"deleteRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"options": {}
			},
			{
				"id": "9nrs0dlc7mm2n83",
				"created": "2023-04-28 21:35:09.617Z",
				"updated": "2024-01-12 20:36:48.287Z",
				"name": "prototypes",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "7uxf5jsx",
						"name": "title",
						"type": "text",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "qmawno0a",
						"name": "project",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "rcazu94eb6tpgkz",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "mweubecx",
						"name": "size",
						"type": "text",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": "^\\d+x\\d+$"
						}
					},
					{
						"system": false,
						"id": "edqxqucx",
						"name": "scale",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": 10,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "ejtce2nr",
						"name": "screensOrder",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "s8jv54yt1tv7zo4",
							"cascadeDelete": false,
							"minSelect": null,
							"maxSelect": null,
							"displayFields": []
						}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_ADI31FC` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (` + "`" + `project` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_LtOn9GS` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE UNIQUE INDEX ` + "`" + `idx_CjtGOPv` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (\n  ` + "`" + `title` + "`" + `,\n  ` + "`" + `project` + "`" + `\n)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= id)\n  ) ||\n  project.users.id ?= @request.auth.id\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= id)\n  ) ||\n  project.users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id &&\n(screensOrder:length = 0 || screensOrder.prototype = id)",
				"updateRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id &&\n(@request.data.project:isset = false || @request.data.project = project) &&\n(\n  @request.data.screensOrder:length = 0 ||\n  @request.data.screensOrder.prototype = id\n)",
				"deleteRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id",
				"options": {}
			},
			{
				"id": "s8jv54yt1tv7zo4",
				"created": "2023-04-28 21:50:39.849Z",
				"updated": "2024-01-12 20:36:48.288Z",
				"name": "screens",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "gxxljwhy",
						"name": "file",
						"type": "file",
						"required": true,
						"presentable": true,
						"unique": false,
						"options": {
							"mimeTypes": [
								"image/jpeg",
								"image/png",
								"image/svg+xml",
								"image/gif",
								"image/webp"
							],
							"thumbs": [
								"450x0",
								"100x100t"
							],
							"maxSelect": 1,
							"maxSize": 7340032,
							"protected": false
						}
					},
					{
						"system": false,
						"id": "xaubj68d",
						"name": "title",
						"type": "text",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "hni4bnuc",
						"name": "prototype",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "9nrs0dlc7mm2n83",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "rmflpnwd",
						"name": "fixedFooter",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "t1oldd0j",
						"name": "fixedHeader",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "5lzvpgij",
						"name": "alignment",
						"type": "select",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"maxSelect": 1,
							"values": [
								"left",
								"center",
								"right"
							]
						}
					},
					{
						"system": false,
						"id": "txu748ez",
						"name": "background",
						"type": "text",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_JddegrA` + "`" + ` ON ` + "`" + `screens` + "`" + ` (` + "`" + `prototype` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_mIRYRIt` + "`" + ` ON ` + "`" + `screens` + "`" + ` (` + "`" + `created` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"updateRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(@request.data.prototype:isset = false || @request.data.prototype = prototype || @request.data.prototype.project.users.id ?= @request.auth.id)",
				"deleteRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"options": {}
			},
			{
				"id": "t5v2n7tnwp9odsm",
				"created": "2023-04-28 22:01:56.882Z",
				"updated": "2024-01-12 20:36:48.288Z",
				"name": "comments",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "8lh7bfcd",
						"name": "screen",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "s8jv54yt1tv7zo4",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "3jsiybxb",
						"name": "resolved",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					},
					{
						"system": false,
						"id": "itj1bx47",
						"name": "replyTo",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "t5v2n7tnwp9odsm",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "ivkzegl2",
						"name": "user",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "_pb_users_auth_",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": []
						}
					},
					{
						"system": false,
						"id": "ysnpdbvr",
						"name": "guestEmail",
						"type": "email",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"exceptDomains": [],
							"onlyDomains": []
						}
					},
					{
						"system": false,
						"id": "pmqjbymi",
						"name": "message",
						"type": "text",
						"required": true,
						"presentable": true,
						"unique": false,
						"options": {
							"min": null,
							"max": 500,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "5n48imri",
						"name": "top",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "qelvd0lz",
						"name": "left",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_2hDH9d1` + "`" + ` ON ` + "`" + `comments` + "`" + ` (` + "`" + `screen` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n  ) || \n  screen.prototype.project.users.id ?= @request.auth.id\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n  ) || \n  screen.prototype.project.users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\n(guestEmail = \"\" || user = \"\") && // either one or the other\n(\n  (\n    guestEmail != \"\" &&\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype) &&\n    guestEmail != screen.prototype.project.users.email\n  ) ||\n  (\n    user = @request.auth.id &&\n    screen.prototype.project.users.id ?= @request.auth.id\n  )\n) &&\n(\n    replyTo = \"\" ||\n    // the reply comment must be from the same screen\n    (replyTo != id && replyTo.screen = screen)\n)",
				"updateRule": "@request.auth.id != \"\" &&\n// disallow changes\n(@request.data.user:isset = false || @request.data.user = user) &&\n(@request.data.guestEmail:isset = false || @request.data.guestEmail = guestEmail) &&\n(@request.data.screen:isset = false || @request.data.screen = screen) &&\n(@request.data.replyTo:isset = false || @request.data.replyTo = replyTo) &&\n(@request.data.message:isset = false || @request.data.message = message) &&\n// check permissions\n(\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype) &&\n    // disallow pin position change from links auths\n    (@request.data.left:isset = false || @request.data.left = left) &&\n    (@request.data.top:isset = false || @request.data.top = top)\n  )\n)",
				"deleteRule": "@request.auth.id != \"\" &&\nscreen.prototype.project.users.id ?= @request.auth.id",
				"options": {}
			},
			{
				"id": "ev1fazzhyrlqmqk",
				"created": "2023-10-03 14:00:50.406Z",
				"updated": "2024-01-12 20:36:48.289Z",
				"name": "notifications",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "y0vu7fjv",
						"name": "user",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "_pb_users_auth_",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "cmjg57vk",
						"name": "comment",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "t5v2n7tnwp9odsm",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "cbey3oyq",
						"name": "read",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					},
					{
						"system": false,
						"id": "kwnfhpxk",
						"name": "processed",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_Sa06MZy` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `user` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_rlJo0z0` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `comment` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_BLd2ajh` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_R3Gthlf` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (\n  ` + "`" + `processed` + "`" + `,\n  ` + "`" + `read` + "`" + `\n)"
				],
				"listRule": "user = @request.auth.id",
				"viewRule": "user = @request.auth.id",
				"createRule": null,
				"updateRule": "user = @request.auth.id &&\n(@request.data.user:isset = false || @request.data.user = user) &&\n(@request.data.comment:isset = false || @request.data.comment = comment)",
				"deleteRule": "user = @request.auth.id",
				"options": {}
			},
			{
				"id": "3zl81stzu1nj1mg",
				"created": "2023-10-10 11:53:13.043Z",
				"updated": "2024-01-12 20:36:48.290Z",
				"name": "hotspotTemplates",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "gmba18ke",
						"name": "title",
						"type": "text",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "4yug4zzo",
						"name": "prototype",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "9nrs0dlc7mm2n83",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "bll0tdno",
						"name": "screens",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "s8jv54yt1tv7zo4",
							"cascadeDelete": false,
							"minSelect": null,
							"maxSelect": null,
							"displayFields": null
						}
					}
				],
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_jWup4CY` + "`" + ` ON ` + "`" + `hotspotTemplates` + "`" + ` (\n  ` + "`" + `title` + "`" + `,\n  ` + "`" + `prototype` + "`" + `\n)",
					"CREATE INDEX ` + "`" + `idx_cS7yE4R` + "`" + ` ON ` + "`" + `hotspotTemplates` + "`" + ` (` + "`" + `prototype` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(screens:length = 0 || screens.prototype = prototype)",
				"updateRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(@request.data.prototype:isset = false || @request.data.prototype = prototype) &&\n(\n  @request.data.screens:length = 0 ||\n  // new screens must be from the template prototype\n  @request.data.screens.prototype = prototype \n)",
				"deleteRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"options": {}
			},
			{
				"id": "i87zsm764hqsyv4",
				"created": "2023-10-10 11:54:40.851Z",
				"updated": "2024-01-12 20:36:48.290Z",
				"name": "hotspots",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "tykp3i4m",
						"name": "screen",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "s8jv54yt1tv7zo4",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "1456cidd",
						"name": "hotspotTemplate",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "3zl81stzu1nj1mg",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "xoahskxp",
						"name": "type",
						"type": "select",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"maxSelect": 1,
							"values": [
								"url",
								"screen",
								"overlay",
								"back",
								"prev",
								"next",
								"scroll",
								"note"
							]
						}
					},
					{
						"system": false,
						"id": "0colbpnz",
						"name": "left",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "ppwfrka5",
						"name": "top",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 0,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "xltmskub",
						"name": "width",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 1,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "vweeoxxq",
						"name": "height",
						"type": "number",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": 1,
							"max": null,
							"noDecimal": false
						}
					},
					{
						"system": false,
						"id": "mgouzrvh",
						"name": "settings",
						"type": "json",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"maxSize": 5242880
						}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_eTQS63F` + "`" + ` ON ` + "`" + `hotspots` + "`" + ` (` + "`" + `hotspotTemplate` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_t84uv81` + "`" + ` ON ` + "`" + `hotspots` + "`" + ` (` + "`" + `screen` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" && (\n  (\n    @request.auth.collectionName = \"links\" && (\n      (\n        screen.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n      ) ||\n      (\n        hotspotTemplate.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= hotspotTemplate.prototype)\n      )\n    )\n  ) ||\n  (\n    @request.auth.collectionName = \"users\" && (\n      screen.prototype.project.users.id ?= @request.auth.id ||\n      hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n    )\n  )\n)",
				"viewRule": "@request.auth.id != \"\" && (\n  (\n    @request.auth.collectionName = \"links\" && (\n      (\n        screen.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n      ) ||\n      (\n        hotspotTemplate.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= hotspotTemplate.prototype)\n      )\n    )\n  ) ||\n  (\n    @request.auth.collectionName = \"users\" && (\n      screen.prototype.project.users.id ?= @request.auth.id ||\n      hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n    )\n  )\n)",
				"createRule": "@request.auth.id != \"\" &&\n(screen != \"\" || hotspotTemplate != \"\") && // at least one of the 2 must be set\n(screen = \"\" || screen.prototype.project.users.id ?= @request.auth.id) &&\n(hotspotTemplate = \"\" || hotspotTemplate.prototype.project.users.id ?= @request.auth.id)",
				"updateRule": "@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n) &&\n// screen change\n(\n  @request.data.screen:isset = false ||\n@request.data.screen = screen ||\n  @request.data.screen.prototype.project.users.id ?= @request.auth.id\n) &&\n// hotspotTemplate change\n(\n  @request.data.hotspotTemplate:isset = false ||\n  @request.data.hotspotTemplate = hotspotTemplate ||\n  @request.data.hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n)",
				"deleteRule": "@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n)",
				"options": {}
			},
			{
				"id": "3khtvy3w0e2bl4v",
				"created": "2023-11-01 05:35:02.975Z",
				"updated": "2024-01-12 20:36:48.291Z",
				"name": "links",
				"type": "auth",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "htofxseu",
						"name": "project",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "rcazu94eb6tpgkz",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "nk04a1v6",
						"name": "onlyPrototypes",
						"type": "relation",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "9nrs0dlc7mm2n83",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": null,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "vi6fymqw",
						"name": "allowComments",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					},
					{
						"system": false,
						"id": "rh3faeow",
						"name": "passwordProtect",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_VV6nRFB` + "`" + ` ON ` + "`" + `links` + "`" + ` (` + "`" + `project` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
				"viewRule": "@request.auth.id != \"\" &&\n@request.auth.id = id || (\n  @request.auth.collectionName = \"users\" &&\n  project.users.id ?= @request.auth.id\n)",
				"createRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id &&\n(onlyPrototypes:length = 0 || onlyPrototypes.project = project)",
				"updateRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id &&\n(@request.data.project:isset = false || @request.data.project = project) &&\n(@request.data.onlyPrototypes:length = 0 || @request.data.onlyPrototypes.project = project)",
				"deleteRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
				"options": {
					"allowEmailAuth": false,
					"allowOAuth2Auth": false,
					"allowUsernameAuth": true,
					"exceptEmailDomains": null,
					"manageRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
					"minPasswordLength": 5,
					"onlyEmailDomains": null,
					"onlyVerified": false,
					"requireEmail": false
				}
			},
			{
				"id": "x0zozna0d5e6voo",
				"created": "2023-11-15 17:58:49.990Z",
				"updated": "2024-01-12 20:36:48.291Z",
				"name": "projectUserPreferences",
				"type": "base",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "bfagylu9",
						"name": "project",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "rcazu94eb6tpgkz",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "mcdwjspv",
						"name": "user",
						"type": "relation",
						"required": true,
						"presentable": false,
						"unique": false,
						"options": {
							"collectionId": "_pb_users_auth_",
							"cascadeDelete": true,
							"minSelect": null,
							"maxSelect": 1,
							"displayFields": null
						}
					},
					{
						"system": false,
						"id": "g3nhqz5i",
						"name": "watch",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					},
					{
						"system": false,
						"id": "nknfxb3d",
						"name": "favorite",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					},
					{
						"system": false,
						"id": "vovea990",
						"name": "lastVisited",
						"type": "date",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": "",
							"max": ""
						}
					}
				],
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_fYy2JOp` + "`" + ` ON ` + "`" + `projectUserPreferences` + "`" + ` (\n  ` + "`" + `project` + "`" + `,\n  ` + "`" + `user` + "`" + `\n)",
					"CREATE INDEX ` + "`" + `idx_vf5x3wr` + "`" + ` ON ` + "`" + `projectUserPreferences` + "`" + ` (` + "`" + `lastVisited` + "`" + `)"
				],
				"listRule": "@request.auth.id = user",
				"viewRule": "@request.auth.id = user",
				"createRule": null,
				"updateRule": "@request.auth.id = user &&\n// disallow changes\n(@request.data.user:isset = false || @request.data.user = user) &&\n(@request.data.project:isset = false || @request.data.project = project) &&\n(@request.data.lastVisited:isset = false || @request.data.lastVisited = lastVisited)",
				"deleteRule": null,
				"options": {}
			},
			{
				"id": "_pb_users_auth_",
				"created": "2024-01-15 13:44:56.786Z",
				"updated": "2024-01-15 13:44:56.802Z",
				"name": "users",
				"type": "auth",
				"system": false,
				"schema": [
					{
						"system": false,
						"id": "users_name",
						"name": "name",
						"type": "text",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {
							"min": null,
							"max": null,
							"pattern": ""
						}
					},
					{
						"system": false,
						"id": "users_avatar",
						"name": "avatar",
						"type": "file",
						"required": false,
						"presentable": true,
						"unique": false,
						"options": {
							"mimeTypes": [
								"image/jpeg",
								"image/png",
								"image/svg+xml",
								"image/gif",
								"image/webp"
							],
							"thumbs": null,
							"maxSelect": 1,
							"maxSize": 5242880,
							"protected": false
						}
					},
					{
						"system": false,
						"id": "e6cqde2q",
						"name": "allowEmailNotifications",
						"type": "bool",
						"required": false,
						"presentable": false,
						"unique": false,
						"options": {}
					}
				],
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_355AEf7` + "`" + ` ON ` + "`" + `users` + "`" + ` (` + "`" + `name` + "`" + `)"
				],
				"listRule": "verified=true &&\n@request.auth.id != \"\" && (\n  @request.auth.collectionName = \"users\"  ||\n  @request.auth.project.users.id ?= id\n)",
				"viewRule": "verified=true &&\n@request.auth.id != \"\" && (\n  @request.auth.collectionName = \"users\"  ||\n  @request.auth.project.users.id ?= id\n)",
				"createRule": "",
				"updateRule": "verified=true &&\n@request.auth.collectionName = \"users\" && id = @request.auth.id",
				"deleteRule": "verified=true &&\n@request.auth.collectionName = \"users\" && id = @request.auth.id",
				"options": {
					"allowEmailAuth": true,
					"allowOAuth2Auth": true,
					"allowUsernameAuth": true,
					"exceptEmailDomains": null,
					"manageRule": null,
					"minPasswordLength": 8,
					"onlyEmailDomains": null,
					"onlyVerified": true,
					"requireEmail": true
				}
			}
		]`

		collections := []*models.Collection{}
		if err := json.Unmarshal([]byte(jsonData), &collections); err != nil {
			return err
		}

		return daos.New(db).ImportCollections(collections, true, nil)
	}, func(db dbx.Builder) error {
		return nil
	})
}
