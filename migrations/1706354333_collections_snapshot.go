package migrations

import (
	"github.com/pocketbase/pocketbase/core"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(app core.App) error {
		jsonData := `[
			{
				"createRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"deleteRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "nlxyjnxl",
						"max": 0,
						"min": 0,
						"name": "title",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "_pb_users_auth_",
						"hidden": false,
						"id": "b7esggok",
						"maxSelect": 2147483647,
						"minSelect": 0,
						"name": "users",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "juozzdkb",
						"name": "archived",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "rcazu94eb6tpgkz",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_K2anj3K` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `archived` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_u6OgtVh` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_0rVVvqv` + "`" + ` ON ` + "`" + `projects` + "`" + ` (` + "`" + `updated` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"name": "projects",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" &&\nusers.id ?= @request.auth.id",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  id = @request.auth.project ||\n  users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id &&\n(screensOrder:length = 0 || screensOrder.prototype = id)",
				"deleteRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "7uxf5jsx",
						"max": 0,
						"min": 0,
						"name": "title",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "rcazu94eb6tpgkz",
						"hidden": false,
						"id": "qmawno0a",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "project",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "mweubecx",
						"max": 0,
						"min": 0,
						"name": "size",
						"pattern": "^\\d+x\\d+$",
						"presentable": false,
						"primaryKey": false,
						"required": false,
						"system": false,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "edqxqucx",
						"max": 10,
						"min": 0,
						"name": "scale",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"cascadeDelete": false,
						"collectionId": "s8jv54yt1tv7zo4",
						"hidden": false,
						"id": "ejtce2nr",
						"maxSelect": 2147483647,
						"minSelect": 0,
						"name": "screensOrder",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "9nrs0dlc7mm2n83",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_ADI31FC` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (` + "`" + `project` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_LtOn9GS` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE UNIQUE INDEX ` + "`" + `idx_CjtGOPv` + "`" + ` ON ` + "`" + `prototypes` + "`" + ` (\n  ` + "`" + `title` + "`" + `,\n  ` + "`" + `project` + "`" + `\n)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= id)\n  ) ||\n  project.users.id ?= @request.auth.id\n)",
				"name": "prototypes",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" &&\nproject.users.id ?= @request.auth.id &&\n(@request.body.project:isset = false || @request.body.project = project) &&\n(\n  @request.body.screensOrder:length = 0 ||\n  @request.body.screensOrder.prototype = id\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= id)\n  ) ||\n  project.users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"deleteRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "gxxljwhy",
						"maxSelect": 1,
						"maxSize": 7340032,
						"mimeTypes": [
							"image/jpeg",
							"image/png",
							"image/svg+xml",
							"image/gif",
							"image/webp"
						],
						"name": "file",
						"presentable": true,
						"protected": false,
						"required": true,
						"system": false,
						"thumbs": [
							"450x0",
							"100x100t"
						],
						"type": "file"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "xaubj68d",
						"max": 0,
						"min": 0,
						"name": "title",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "9nrs0dlc7mm2n83",
						"hidden": false,
						"id": "hni4bnuc",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "prototype",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "rmflpnwd",
						"max": null,
						"min": 0,
						"name": "fixedFooter",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "t1oldd0j",
						"max": null,
						"min": 0,
						"name": "fixedHeader",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "5lzvpgij",
						"maxSelect": 1,
						"name": "alignment",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "select",
						"values": [
							"left",
							"center",
							"right"
						]
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "txu748ez",
						"max": 0,
						"min": 0,
						"name": "background",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": false,
						"system": false,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "s8jv54yt1tv7zo4",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_JddegrA` + "`" + ` ON ` + "`" + `screens` + "`" + ` (` + "`" + `prototype` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_mIRYRIt` + "`" + ` ON ` + "`" + `screens` + "`" + ` (` + "`" + `created` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"name": "screens",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(@request.body.prototype:isset = false || @request.body.prototype = prototype || @request.body.prototype.project.users.id ?= @request.auth.id)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": "@request.auth.id != \"\" &&\n(guestEmail = \"\" || user = \"\") && // either one or the other\n(\n  (\n    guestEmail != \"\" &&\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype) &&\n    guestEmail != screen.prototype.project.users.email\n  ) ||\n  (\n    user = @request.auth.id &&\n    screen.prototype.project.users.id ?= @request.auth.id\n  )\n) &&\n(\n    replyTo = \"\" ||\n    // the reply comment must be from the same screen\n    (replyTo != id && replyTo.screen = screen)\n)",
				"deleteRule": "@request.auth.id != \"\" &&\nscreen.prototype.project.users.id ?= @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "s8jv54yt1tv7zo4",
						"hidden": false,
						"id": "8lh7bfcd",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "screen",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "3jsiybxb",
						"name": "resolved",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"cascadeDelete": true,
						"collectionId": "t5v2n7tnwp9odsm",
						"hidden": false,
						"id": "itj1bx47",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "replyTo",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": true,
						"collectionId": "_pb_users_auth_",
						"hidden": false,
						"id": "ivkzegl2",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "user",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"exceptDomains": null,
						"hidden": false,
						"id": "ysnpdbvr",
						"name": "guestEmail",
						"onlyDomains": null,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "email"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "pmqjbymi",
						"max": 500,
						"min": 0,
						"name": "message",
						"pattern": "",
						"presentable": true,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "5n48imri",
						"max": null,
						"min": 0,
						"name": "top",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "qelvd0lz",
						"max": null,
						"min": 0,
						"name": "left",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "t5v2n7tnwp9odsm",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_2hDH9d1` + "`" + ` ON ` + "`" + `comments` + "`" + ` (` + "`" + `screen` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n  ) || \n  screen.prototype.project.users.id ?= @request.auth.id\n)",
				"name": "comments",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" &&\n// disallow changes\n(@request.body.user:isset = false || @request.body.user = user) &&\n(@request.body.guestEmail:isset = false || @request.body.guestEmail = guestEmail) &&\n(@request.body.screen:isset = false || @request.body.screen = screen) &&\n(@request.body.replyTo:isset = false || @request.body.replyTo = replyTo) &&\n(@request.body.message:isset = false || @request.body.message = message) &&\n// check permissions\n(\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype) &&\n    // disallow pin position change from links auths\n    (@request.body.left:isset = false || @request.body.left = left) &&\n    (@request.body.top:isset = false || @request.body.top = top)\n  )\n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    @request.auth.allowComments = true &&\n    screen.prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n  ) || \n  screen.prototype.project.users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": null,
				"deleteRule": "user = @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "_pb_users_auth_",
						"hidden": false,
						"id": "y0vu7fjv",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "user",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": true,
						"collectionId": "t5v2n7tnwp9odsm",
						"hidden": false,
						"id": "cmjg57vk",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "comment",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "cbey3oyq",
						"name": "read",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "kwnfhpxk",
						"name": "processed",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "ev1fazzhyrlqmqk",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_Sa06MZy` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `user` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_rlJo0z0` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `comment` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_BLd2ajh` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (` + "`" + `created` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_R3Gthlf` + "`" + ` ON ` + "`" + `notifications` + "`" + ` (\n  ` + "`" + `processed` + "`" + `,\n  ` + "`" + `read` + "`" + `\n)"
				],
				"listRule": "user = @request.auth.id",
				"name": "notifications",
				"system": false,
				"type": "base",
				"updateRule": "user = @request.auth.id &&\n(@request.body.user:isset = false || @request.body.user = user) &&\n(@request.body.comment:isset = false || @request.body.comment = comment)",
				"viewRule": "user = @request.auth.id"
			},
			{
				"createRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(screens:length = 0 || screens.prototype = prototype)",
				"deleteRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "gmba18ke",
						"max": 0,
						"min": 0,
						"name": "title",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "9nrs0dlc7mm2n83",
						"hidden": false,
						"id": "4yug4zzo",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "prototype",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": false,
						"collectionId": "s8jv54yt1tv7zo4",
						"hidden": false,
						"id": "bll0tdno",
						"maxSelect": 2147483647,
						"minSelect": 0,
						"name": "screens",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "3zl81stzu1nj1mg",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_jWup4CY` + "`" + ` ON ` + "`" + `hotspotTemplates` + "`" + ` (\n  ` + "`" + `title` + "`" + `,\n  ` + "`" + `prototype` + "`" + `\n)",
					"CREATE INDEX ` + "`" + `idx_cS7yE4R` + "`" + ` ON ` + "`" + `hotspotTemplates` + "`" + ` (` + "`" + `prototype` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)",
				"name": "hotspotTemplates",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" &&\nprototype.project.users.id ?= @request.auth.id &&\n(@request.body.prototype:isset = false || @request.body.prototype = prototype) &&\n(\n  @request.body.screens:length = 0 ||\n  // new screens must be from the template prototype\n  @request.body.screens.prototype = prototype \n)",
				"viewRule": "@request.auth.id != \"\" &&\n(\n  (\n    prototype.project = @request.auth.project &&\n    (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= prototype)\n  ) ||\n  prototype.project.users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": "@request.auth.id != \"\" &&\n(screen != \"\" || hotspotTemplate != \"\") && // at least one of the 2 must be set\n(screen = \"\" || screen.prototype.project.users.id ?= @request.auth.id) &&\n(hotspotTemplate = \"\" || hotspotTemplate.prototype.project.users.id ?= @request.auth.id)",
				"deleteRule": "@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n)",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "s8jv54yt1tv7zo4",
						"hidden": false,
						"id": "tykp3i4m",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "screen",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": true,
						"collectionId": "3zl81stzu1nj1mg",
						"hidden": false,
						"id": "1456cidd",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "hotspotTemplate",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "xoahskxp",
						"maxSelect": 1,
						"name": "type",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "select",
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
					},
					{
						"hidden": false,
						"id": "0colbpnz",
						"max": null,
						"min": 0,
						"name": "left",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "ppwfrka5",
						"max": null,
						"min": 0,
						"name": "top",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "xltmskub",
						"max": null,
						"min": 1,
						"name": "width",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "vweeoxxq",
						"max": null,
						"min": 1,
						"name": "height",
						"onlyInt": false,
						"presentable": false,
						"required": false,
						"system": false,
						"type": "number"
					},
					{
						"hidden": false,
						"id": "mgouzrvh",
						"maxSize": 5242880,
						"name": "settings",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "json"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "i87zsm764hqsyv4",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_eTQS63F` + "`" + ` ON ` + "`" + `hotspots` + "`" + ` (` + "`" + `hotspotTemplate` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_t84uv81` + "`" + ` ON ` + "`" + `hotspots` + "`" + ` (` + "`" + `screen` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" && (\n  (\n    @request.auth.collectionName = \"links\" && (\n      (\n        screen.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n      ) ||\n      (\n        hotspotTemplate.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= hotspotTemplate.prototype)\n      )\n    )\n  ) ||\n  (\n    @request.auth.collectionName = \"users\" && (\n      screen.prototype.project.users.id ?= @request.auth.id ||\n      hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n    )\n  )\n)",
				"name": "hotspots",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id != \"\" && (\n  screen.prototype.project.users.id ?= @request.auth.id ||\n  hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n) &&\n// screen change\n(\n  @request.body.screen:isset = false ||\n  @request.body.screen = screen ||\n  @request.body.screen.prototype.project.users.id ?= @request.auth.id ||\n  (@request.body.screen = \"\" && @request.body.hotspotTemplate != \"\")\n) &&\n// hotspotTemplate change\n(\n  @request.body.hotspotTemplate:isset = false ||\n  @request.body.hotspotTemplate = hotspotTemplate ||\n  @request.body.hotspotTemplate.prototype.project.users.id ?= @request.auth.id ||\n  (@request.body.hotspotTemplate = \"\" && @request.body.screen != \"\")\n)",
				"viewRule": "@request.auth.id != \"\" && (\n  (\n    @request.auth.collectionName = \"links\" && (\n      (\n        screen.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= screen.prototype)\n      ) ||\n      (\n        hotspotTemplate.prototype.project = @request.auth.project &&\n        (@request.auth.onlyPrototypes:length = 0 || @request.auth.onlyPrototypes.id ?= hotspotTemplate.prototype)\n      )\n    )\n  ) ||\n  (\n    @request.auth.collectionName = \"users\" && (\n      screen.prototype.project.users.id ?= @request.auth.id ||\n      hotspotTemplate.prototype.project.users.id ?= @request.auth.id\n    )\n  )\n)"
			},
			{
				"authAlert": {
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>We noticed a login to your {APP_NAME} account from a new location.</p>\n<p>If this was you, you may disregard this email.</p>\n<p><strong>If this wasn't you, you should immediately change your {APP_NAME} account password to revoke access from all other locations.</strong></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "Login from a new location"
					},
					"enabled": false
				},
				"authRule": "",
				"authToken": {
					"duration": 1209600
				},
				"confirmEmailChangeTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to confirm your new email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-email-change/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Confirm new email</a>\n</p>\n<p><i>If you didn't ask to change your email address, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Confirm your {APP_NAME} new email address"
				},
				"createRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id &&\n(onlyPrototypes:length = 0 || onlyPrototypes.project = project)",
				"deleteRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
				"emailChangeToken": {
					"duration": 1800
				},
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cost": 10,
						"hidden": true,
						"id": "password901924565",
						"max": 0,
						"min": 5,
						"name": "password",
						"pattern": "",
						"presentable": false,
						"required": true,
						"system": true,
						"type": "password"
					},
					{
						"autogeneratePattern": "[a-zA-Z0-9_]{50}",
						"hidden": true,
						"id": "text2504183744",
						"max": 60,
						"min": 30,
						"name": "tokenKey",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"exceptDomains": null,
						"hidden": false,
						"id": "email3885137012",
						"name": "email",
						"onlyDomains": null,
						"presentable": false,
						"required": false,
						"system": true,
						"type": "email"
					},
					{
						"hidden": false,
						"id": "bool1547992806",
						"name": "emailVisibility",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "bool256245529",
						"name": "verified",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"autogeneratePattern": "users[0-9]{6}",
						"hidden": false,
						"id": "text4166911607",
						"max": 150,
						"min": 3,
						"name": "username",
						"pattern": "^[\\w][\\w\\.\\-]*$",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "rcazu94eb6tpgkz",
						"hidden": false,
						"id": "htofxseu",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "project",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": true,
						"collectionId": "9nrs0dlc7mm2n83",
						"hidden": false,
						"id": "nk04a1v6",
						"maxSelect": 2147483647,
						"minSelect": 0,
						"name": "onlyPrototypes",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "vi6fymqw",
						"name": "allowComments",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "rh3faeow",
						"name": "passwordProtect",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"fileToken": {
					"duration": 120
				},
				"id": "3khtvy3w0e2bl4v",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `_3khtvy3w0e2bl4v_username_idx` + "`" + ` ON ` + "`" + `links` + "`" + ` (username COLLATE NOCASE)",
					"CREATE UNIQUE INDEX ` + "`" + `_3khtvy3w0e2bl4v_email_idx` + "`" + ` ON ` + "`" + `links` + "`" + ` (` + "`" + `email` + "`" + `) WHERE ` + "`" + `email` + "`" + ` != ''",
					"CREATE UNIQUE INDEX ` + "`" + `_3khtvy3w0e2bl4v_tokenKey_idx` + "`" + ` ON ` + "`" + `links` + "`" + ` (` + "`" + `tokenKey` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_VV6nRFB` + "`" + ` ON ` + "`" + `links` + "`" + ` (` + "`" + `project` + "`" + `)"
				],
				"listRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
				"manageRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id",
				"mfa": {
					"duration": 1800,
					"enabled": false,
					"rule": ""
				},
				"name": "links",
				"oauth2": {
					"enabled": false,
					"mappedFields": {
						"avatarURL": "",
						"id": "",
						"name": "",
						"username": "username"
					}
				},
				"otp": {
					"duration": 180,
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>Your one-time password is: <strong>{OTP}</strong></p>\n<p><i>If you didn't ask for the one-time password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "OTP for {APP_NAME}"
					},
					"enabled": false,
					"length": 8
				},
				"passwordAuth": {
					"enabled": true,
					"identityFields": [
						"username"
					]
				},
				"passwordResetToken": {
					"duration": 1800
				},
				"resetPasswordTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to reset your password.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-password-reset/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Reset password</a>\n</p>\n<p><i>If you didn't ask to reset your password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Reset your {APP_NAME} password"
				},
				"system": false,
				"type": "auth",
				"updateRule": "@request.auth.id != \"\" &&\n@request.auth.collectionName = \"users\" &&\nproject.users.id ?= @request.auth.id &&\n(@request.body.project:isset = false || @request.body.project = project) &&\n(@request.body.onlyPrototypes:length = 0 || @request.body.onlyPrototypes.project = project)",
				"verificationTemplate": {
					"body": "<p>Hello,</p>\n<p>Thank you for joining us at {APP_NAME}.</p>\n<p>Click on the button below to verify your email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-verification/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Verify</a>\n</p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Verify your {APP_NAME} email"
				},
				"verificationToken": {
					"duration": 604800
				},
				"viewRule": "@request.auth.id != \"\" &&\n@request.auth.id = id || (\n  @request.auth.collectionName = \"users\" &&\n  project.users.id ?= @request.auth.id\n)"
			},
			{
				"createRule": null,
				"deleteRule": null,
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cascadeDelete": true,
						"collectionId": "rcazu94eb6tpgkz",
						"hidden": false,
						"id": "bfagylu9",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "project",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"cascadeDelete": true,
						"collectionId": "_pb_users_auth_",
						"hidden": false,
						"id": "mcdwjspv",
						"maxSelect": 1,
						"minSelect": 0,
						"name": "user",
						"presentable": false,
						"required": true,
						"system": false,
						"type": "relation"
					},
					{
						"hidden": false,
						"id": "g3nhqz5i",
						"name": "watch",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "nknfxb3d",
						"name": "favorite",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "vovea990",
						"max": "",
						"min": "",
						"name": "lastVisited",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "date"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"id": "x0zozna0d5e6voo",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_fYy2JOp` + "`" + ` ON ` + "`" + `projectUserPreferences` + "`" + ` (\n  ` + "`" + `project` + "`" + `,\n  ` + "`" + `user` + "`" + `\n)",
					"CREATE INDEX ` + "`" + `idx_vf5x3wr` + "`" + ` ON ` + "`" + `projectUserPreferences` + "`" + ` (` + "`" + `lastVisited` + "`" + `)"
				],
				"listRule": "@request.auth.id = user",
				"name": "projectUserPreferences",
				"system": false,
				"type": "base",
				"updateRule": "@request.auth.id = user &&\n// disallow changes\n(@request.body.user:isset = false || @request.body.user = user) &&\n(@request.body.project:isset = false || @request.body.project = project) &&\n(@request.body.lastVisited:isset = false || @request.body.lastVisited = lastVisited)",
				"viewRule": "@request.auth.id = user"
			},
			{
				"authAlert": {
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>We noticed a login to your {APP_NAME} account from a new location.</p>\n<p>If this was you, you may disregard this email.</p>\n<p><strong>If this wasn't you, you should immediately change your {APP_NAME} account password to revoke access from all other locations.</strong></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "Login from a new location"
					},
					"enabled": false
				},
				"authRule": "verified=true",
				"authToken": {
					"duration": 1209600
				},
				"confirmEmailChangeTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to confirm your new email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-email-change/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Confirm new email</a>\n</p>\n<p><i>If you didn't ask to change your email address, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Confirm your {APP_NAME} new email address"
				},
				"createRule": "",
				"deleteRule": "verified=true &&\n@request.auth.collectionName = \"users\" && id = @request.auth.id",
				"emailChangeToken": {
					"duration": 1800
				},
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cost": 10,
						"hidden": true,
						"id": "password901924565",
						"max": 0,
						"min": 8,
						"name": "password",
						"pattern": "",
						"presentable": false,
						"required": true,
						"system": true,
						"type": "password"
					},
					{
						"autogeneratePattern": "[a-zA-Z0-9_]{50}",
						"hidden": true,
						"id": "text2504183744",
						"max": 60,
						"min": 30,
						"name": "tokenKey",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"exceptDomains": null,
						"hidden": false,
						"id": "email3885137012",
						"name": "email",
						"onlyDomains": null,
						"presentable": false,
						"required": true,
						"system": true,
						"type": "email"
					},
					{
						"hidden": false,
						"id": "bool1547992806",
						"name": "emailVisibility",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "bool256245529",
						"name": "verified",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"autogeneratePattern": "users[0-9]{6}",
						"hidden": false,
						"id": "text4166911607",
						"max": 150,
						"min": 3,
						"name": "username",
						"pattern": "^[\\w][\\w\\.\\-]*$",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": false,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "users_name",
						"max": 0,
						"min": 0,
						"name": "name",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": false,
						"system": false,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "users_avatar",
						"maxSelect": 1,
						"maxSize": 5242880,
						"mimeTypes": [
							"image/jpeg",
							"image/png",
							"image/svg+xml",
							"image/gif",
							"image/webp"
						],
						"name": "avatar",
						"presentable": true,
						"protected": false,
						"required": false,
						"system": false,
						"thumbs": null,
						"type": "file"
					},
					{
						"hidden": false,
						"id": "e6cqde2q",
						"name": "allowEmailNotifications",
						"presentable": false,
						"required": false,
						"system": false,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": false,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": false,
						"type": "autodate"
					}
				],
				"fileToken": {
					"duration": 120
				},
				"id": "_pb_users_auth_",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `__pb_users_auth__username_idx` + "`" + ` ON ` + "`" + `users` + "`" + ` (username COLLATE NOCASE)",
					"CREATE UNIQUE INDEX ` + "`" + `__pb_users_auth__email_idx` + "`" + ` ON ` + "`" + `users` + "`" + ` (` + "`" + `email` + "`" + `) WHERE ` + "`" + `email` + "`" + ` != ''",
					"CREATE UNIQUE INDEX ` + "`" + `__pb_users_auth__tokenKey_idx` + "`" + ` ON ` + "`" + `users` + "`" + ` (` + "`" + `tokenKey` + "`" + `)",
					"CREATE INDEX ` + "`" + `idx_355AEf7` + "`" + ` ON ` + "`" + `users` + "`" + ` (` + "`" + `name` + "`" + `)"
				],
				"listRule": "verified=true &&\n@request.auth.id != \"\" && (\n  @request.auth.collectionName = \"users\"  ||\n  @request.auth.project.users.id ?= id\n)",
				"manageRule": null,
				"mfa": {
					"duration": 1800,
					"enabled": false,
					"rule": ""
				},
				"name": "users",
				"oauth2": {
					"enabled": false,
					"mappedFields": {
						"avatarURL": "",
						"id": "",
						"name": "",
						"username": "username"
					}
				},
				"otp": {
					"duration": 180,
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>Your one-time password is: <strong>{OTP}</strong></p>\n<p><i>If you didn't ask for the one-time password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "OTP for {APP_NAME}"
					},
					"enabled": false,
					"length": 8
				},
				"passwordAuth": {
					"enabled": true,
					"identityFields": [
						"email",
						"username"
					]
				},
				"passwordResetToken": {
					"duration": 1800
				},
				"resetPasswordTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to reset your password.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-password-reset/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Reset password</a>\n</p>\n<p><i>If you didn't ask to reset your password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Reset your {APP_NAME} password"
				},
				"system": false,
				"type": "auth",
				"updateRule": "verified=true &&\n@request.auth.collectionName = \"users\" && id = @request.auth.id",
				"verificationTemplate": {
					"body": "<p>Hello,</p>\n<p>Thank you for joining us at {APP_NAME}.</p>\n<p>Click on the button below to verify your email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-verification/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Verify</a>\n</p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Verify your {APP_NAME} email"
				},
				"verificationToken": {
					"duration": 604800
				},
				"viewRule": "verified=true &&\n@request.auth.id != \"\" && (\n  @request.auth.collectionName = \"users\"  ||\n  @request.auth.project.users.id ?= id\n)"
			},
			{
				"authAlert": {
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>We noticed a login to your {APP_NAME} account from a new location.</p>\n<p>If this was you, you may disregard this email.</p>\n<p><strong>If this wasn't you, you should immediately change your {APP_NAME} account password to revoke access from all other locations.</strong></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "Login from a new location"
					},
					"enabled": true
				},
				"authRule": "",
				"authToken": {
					"duration": 1209600
				},
				"confirmEmailChangeTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to confirm your new email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-email-change/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Confirm new email</a>\n</p>\n<p><i>If you didn't ask to change your email address, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Confirm your {APP_NAME} new email address"
				},
				"createRule": null,
				"deleteRule": null,
				"emailChangeToken": {
					"duration": 1800
				},
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cost": 0,
						"hidden": true,
						"id": "password901924565",
						"max": 0,
						"min": 8,
						"name": "password",
						"pattern": "",
						"presentable": false,
						"required": true,
						"system": true,
						"type": "password"
					},
					{
						"autogeneratePattern": "[a-zA-Z0-9]{50}",
						"hidden": true,
						"id": "text2504183744",
						"max": 60,
						"min": 30,
						"name": "tokenKey",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"exceptDomains": null,
						"hidden": false,
						"id": "email3885137012",
						"name": "email",
						"onlyDomains": null,
						"presentable": false,
						"required": true,
						"system": true,
						"type": "email"
					},
					{
						"hidden": false,
						"id": "bool1547992806",
						"name": "emailVisibility",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "bool256245529",
						"name": "verified",
						"presentable": false,
						"required": false,
						"system": true,
						"type": "bool"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": true,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": true,
						"type": "autodate"
					}
				],
				"fileToken": {
					"duration": 120
				},
				"id": "pbc_3142635823",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_tokenKey_pbc_3142635823` + "`" + ` ON ` + "`" + `_superusers` + "`" + ` (` + "`" + `tokenKey` + "`" + `)",
					"CREATE UNIQUE INDEX ` + "`" + `idx_email_pbc_3142635823` + "`" + ` ON ` + "`" + `_superusers` + "`" + ` (` + "`" + `email` + "`" + `) WHERE ` + "`" + `email` + "`" + ` != ''"
				],
				"listRule": null,
				"manageRule": null,
				"mfa": {
					"duration": 1800,
					"enabled": false,
					"rule": ""
				},
				"name": "_superusers",
				"oauth2": {
					"enabled": false,
					"mappedFields": {
						"avatarURL": "",
						"id": "",
						"name": "",
						"username": ""
					}
				},
				"otp": {
					"duration": 180,
					"emailTemplate": {
						"body": "<p>Hello,</p>\n<p>Your one-time password is: <strong>{OTP}</strong></p>\n<p><i>If you didn't ask for the one-time password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
						"subject": "OTP for {APP_NAME}"
					},
					"enabled": false,
					"length": 8
				},
				"passwordAuth": {
					"enabled": true,
					"identityFields": [
						"email"
					]
				},
				"passwordResetToken": {
					"duration": 1800
				},
				"resetPasswordTemplate": {
					"body": "<p>Hello,</p>\n<p>Click on the button below to reset your password.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-password-reset/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Reset password</a>\n</p>\n<p><i>If you didn't ask to reset your password, you can ignore this email.</i></p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Reset your {APP_NAME} password"
				},
				"system": true,
				"type": "auth",
				"updateRule": null,
				"verificationTemplate": {
					"body": "<p>Hello,</p>\n<p>Thank you for joining us at {APP_NAME}.</p>\n<p>Click on the button below to verify your email address.</p>\n<p>\n  <a class=\"btn\" href=\"{APP_URL}/_/#/auth/confirm-verification/{TOKEN}\" target=\"_blank\" rel=\"noopener\">Verify</a>\n</p>\n<p>\n  Thanks,<br/>\n  {APP_NAME} team\n</p>",
					"subject": "Verify your {APP_NAME} email"
				},
				"verificationToken": {
					"duration": 259200
				},
				"viewRule": null
			},
			{
				"createRule": null,
				"deleteRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text455797646",
						"max": 0,
						"min": 0,
						"name": "collectionRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text127846527",
						"max": 0,
						"min": 0,
						"name": "recordRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text2462348188",
						"max": 0,
						"min": 0,
						"name": "provider",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text1044722854",
						"max": 0,
						"min": 0,
						"name": "providerId",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": true,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": true,
						"type": "autodate"
					}
				],
				"id": "pbc_2281828961",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_externalAuths_record_provider` + "`" + ` ON ` + "`" + `_externalAuths` + "`" + ` (collectionRef, recordRef, provider)",
					"CREATE UNIQUE INDEX ` + "`" + `idx_externalAuths_collection_provider` + "`" + ` ON ` + "`" + `_externalAuths` + "`" + ` (collectionRef, provider, providerId)"
				],
				"listRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"name": "_externalAuths",
				"system": true,
				"type": "base",
				"updateRule": null,
				"viewRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId"
			},
			{
				"createRule": null,
				"deleteRule": null,
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text455797646",
						"max": 0,
						"min": 0,
						"name": "collectionRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text127846527",
						"max": 0,
						"min": 0,
						"name": "recordRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text1582905952",
						"max": 0,
						"min": 0,
						"name": "method",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": true,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": true,
						"type": "autodate"
					}
				],
				"id": "pbc_2279338944",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_mfas_collectionRef_recordRef` + "`" + ` ON ` + "`" + `_mfas` + "`" + ` (collectionRef,recordRef)"
				],
				"listRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"name": "_mfas",
				"system": true,
				"type": "base",
				"updateRule": null,
				"viewRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId"
			},
			{
				"createRule": null,
				"deleteRule": null,
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text455797646",
						"max": 0,
						"min": 0,
						"name": "collectionRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text127846527",
						"max": 0,
						"min": 0,
						"name": "recordRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"cost": 8,
						"hidden": true,
						"id": "password901924565",
						"max": 0,
						"min": 0,
						"name": "password",
						"pattern": "",
						"presentable": false,
						"required": true,
						"system": true,
						"type": "password"
					},
					{
						"autogeneratePattern": "",
						"hidden": true,
						"id": "text3866985172",
						"max": 0,
						"min": 0,
						"name": "sentTo",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": false,
						"system": true,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": true,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": true,
						"type": "autodate"
					}
				],
				"id": "pbc_1638494021",
				"indexes": [
					"CREATE INDEX ` + "`" + `idx_otps_collectionRef_recordRef` + "`" + ` ON ` + "`" + `_otps` + "`" + ` (collectionRef, recordRef)"
				],
				"listRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"name": "_otps",
				"system": true,
				"type": "base",
				"updateRule": null,
				"viewRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId"
			},
			{
				"createRule": null,
				"deleteRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"fields": [
					{
						"autogeneratePattern": "[a-z0-9]{15}",
						"hidden": false,
						"id": "text3208210256",
						"max": 15,
						"min": 15,
						"name": "id",
						"pattern": "^[a-z0-9]+$",
						"presentable": false,
						"primaryKey": true,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text455797646",
						"max": 0,
						"min": 0,
						"name": "collectionRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text127846527",
						"max": 0,
						"min": 0,
						"name": "recordRef",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"autogeneratePattern": "",
						"hidden": false,
						"id": "text4228609354",
						"max": 0,
						"min": 0,
						"name": "fingerprint",
						"pattern": "",
						"presentable": false,
						"primaryKey": false,
						"required": true,
						"system": true,
						"type": "text"
					},
					{
						"hidden": false,
						"id": "autodate2990389176",
						"name": "created",
						"onCreate": true,
						"onUpdate": false,
						"presentable": false,
						"system": true,
						"type": "autodate"
					},
					{
						"hidden": false,
						"id": "autodate3332085495",
						"name": "updated",
						"onCreate": true,
						"onUpdate": true,
						"presentable": false,
						"system": true,
						"type": "autodate"
					}
				],
				"id": "pbc_4275539003",
				"indexes": [
					"CREATE UNIQUE INDEX ` + "`" + `idx_authOrigins_unique_pairs` + "`" + ` ON ` + "`" + `_authOrigins` + "`" + ` (collectionRef, recordRef, fingerprint)"
				],
				"listRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId",
				"name": "_authOrigins",
				"system": true,
				"type": "base",
				"updateRule": null,
				"viewRule": "@request.auth.id != '' && recordRef = @request.auth.id && collectionRef = @request.auth.collectionId"
			}
		]`

		return app.ImportCollectionsByMarshaledJSON([]byte(jsonData), true)
	}, func(app core.App) error {
		return nil
	})
}
