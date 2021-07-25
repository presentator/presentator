Presentator v2 REST API JS client
======================================================================

Simple and compact (~7kb gzip) JavaScript client (browser and node.js) for the Presentator v2 REST API (based on [axios](https://github.com/axios/axios) HTTP client).

- [Installing](#installing)
- [Usage](#usage)
- [Development](#development)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Installing

#### Using npm
```bash
npm install presentator-client --save
```

*ES6-style import:*
```js
import PresentatorClient from 'presentator-client'
```

#### Manual
```html
<script src="/path/to/dist/client.min.js"></script>
```

#### Example
```js
var client = new PresentatorClient('my_api_url');

client.Users.login('test@example.com', 'my-password').then(function (data) {
    // success...
    // see axios response schema - https://github.com/axios/axios#response-schema
}).catch(function (e) {
    // error...
});
```


## Usage

#### Creating new client instance

```js
var client = new PresentatorClient(baseUrl = '', token = '', axiosConfig = {});
```

#### Instance methods

> Each instance method returns the `PresentatorClient` instance to allow chaining.

| Method                                         | Description                                             |
|:-----------------------------------------------|:--------------------------------------------------------|
| `client.setBaseUrl(url)`                       | Sets the http client base url address.                  |
| `client.setToken(token = '')`                  | Sets or removes `Authorization` request header.         |
| `client.setLanguage(lang = 'en-US')`           | Sets or removes `Accept-Language` request header.       |
| `client.enableAutoCancellation(enable = true)` | Enables or disables cancellation of duplicated requests |
| `client.cancelRequest(cancelKey)`              | Cancels single request by its cancellation token.       |

#### API resources/services

> Each resource call returns a `Promise` object.
> More detailed API docs related to the resources could be found [here](https://presentator.io/docs).

| Resource                                                                                                                        | Description                                                                                     |
|:--------------------------------------------------------------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------|
| *[Previews](https://presentator.io/docs/preview)*                                                                               |                                                                                                 |
| &#x1f513; `client.Previews.authorize(slug, password = '', bodyParams = {}, queryParams = {})`                                   | Generates a project preview token and authorizes access to a project link.                      |
| &#x1f513; `client.Previews.getOne(previewToken, queryParams = {})`                                                              | Returns summary of a project preview                                                            |
| &#x1f513; `client.Previews.getPrototype(previewToken, id, queryParams = {})`                                                    | Returns preview details for the specified project link prototype.                               |
| &#x1f513; `client.Previews.getAssets(previewToken, queryParams = {})`                                                           | Returns list with all project guideline sections and assets.                                    |
| &#x1f513; `client.Previews.getScreenCommentsList(previewToken, page = 1, perPage = 20, queryParams = {})`                       | Returns list with all project preview screen comments.                                          |
| &#x1f513; `client.Previews.createScreenComment(previewToken, bodyParams = {}, queryParams = {})`                                | Creates a new screen comment for a preview screen.                                              |
| &#x1f513; `client.Previews.updateScreenComment(previewToken, id, bodyParams = {}, queryParams = {})`                            | Updates the status of a primary screen comment within the preview screens.                      |
| &#x1f513; `client.Previews.report(previewToken, details = '', bodyParams = {}, queryParams = {})`                               | Reports a project link for spam, malware or other abusive content.                              |
| *[Users](https://presentator.io/docs/users)*                                                                                    |                                                                                                 |
| &#x1f513; `client.Users.getAuthMethods(queryParams = {})`                                                                       | Returns array list with all configured application auth methods and clients.                    |
| &#x1f513; `client.Users.getAuthClients(queryParams = {})`                                                                       | (**DEPRECATED**) Returns array list with all configured application auth clients.               |
| &#x1f513; `client.Users.authorizeAuthClient(client, code, bodyParams = {}, queryParams = {})`                                   | Authorizes a user via auth client and generates new user authorization token.                   |
| &#x1f513; `client.Users.register(bodyParams = {}, queryParams = {})`                                                            | Registers a new user (aka. creates an inactive regular user).                                   |
| &#x1f513; `client.Users.activate(activationToken, bodyParams = {}, queryParams = {})`                                           | Activates an inactive User model associated with the provided activation token.                 |
| &#x1f513; `client.Users.login(email, password, bodyParams = {}, queryParams = {})`                                              | Performs active User model authorization.                                                       |
| &#x1f513; `client.Users.requestPasswordReset(email, bodyParams = {}, queryParams = {})`                                         | Sends a forgotten password email.                                                               |
| &#x1f513; `client.Users.confirmPasswordReset(passwordResetToken, password, passwordConfirm, bodyParams = {}, queryParams = {})` | Resets the password for a single user by a password reset token.                                |
| &#x1f512; `client.Users.refresh(bodyParams = {}, queryParams = {})`                                                             | Refreshes user authorization token.                                                             |
| &#x1f512; `client.Users.requestEmailChange(email, bodyParams = {}, queryParams = {})`                                           | Sends a request to change the authorized user's email address.                                  |
| &#x1f512; `client.Users.confirmEmailChange(emailChangeToken, bodyParams = {}, queryParams = {})`                                | Confirms authorized user's email address change.                                                |
| &#x1f512; `client.Users.sendFeedback(message, bodyParams = {}, queryParams = {})`                                               | Sends user's feedback for Presentator to support.                                               |
| &#x1f512; `client.Users.getList(page = 1, perPage = 20, queryParams = {})`                                                      | Returns paginated users list (super users only).                                                |
| &#x1f512; `client.Users.getOne(id, queryParams = {})`                                                                           | Views single user.                                                                              |
| &#x1f512; `client.Users.create(bodyParams = {}, queryParams = {})`                                                              | Creates a new user (super users only).                                                          |
| &#x1f512; `client.Users.update(id, bodyParams = {}, queryParams = {})`                                                          | Updates an existing user.                                                                       |
| &#x1f512; `client.Users.delete(id, bodyParams = {}, queryParams = {})`                                                          | Deletes an existing user.                                                                       |
| *[Projects](https://presentator.io/docs/projects)*                                                                              |                                                                                                 |
| &#x1f512; `client.Projects.getList(page = 1, perPage = 20, queryParams = {})`                                                   | Returns paginated projects list.                                                                |
| &#x1f512; `client.Projects.getOne(id, queryParams = {})`                                                                        | Views single project.                                                                           |
| &#x1f512; `client.Projects.create(bodyParams = {}, queryParams = {})`                                                           | Creates a new project and automatically assign the current authorized user as an administrator. |
| &#x1f512; `client.Projects.update(id, bodyParams = {}, queryParams = {})`                                                       | Updates an existing project.                                                                    |
| &#x1f512; `client.Projects.delete(id, bodyParams = {}, queryParams = {})`                                                       | Deletes an existing project.                                                                    |
| &#x1f512; `client.Projects.getCollaboratorsList(id, queryParams = {})`                                                          | Returns list with all project's collaborators (including guests).                               |
| &#x1f512; `client.Projects.searchUsers(id, searchTerm, queryParams = {})`                                                       | Searches for new project admins (project linked users).                                         |
| &#x1f512; `client.Projects.getUsersList(id, queryParams = {})`                                                                  | Returns list with all linked project users.                                                     |
| &#x1f512; `client.Projects.linkUser(id, userId, bodyParams = {}, queryParams = {})`                                             | Links an active user to a project (aka. adding new project admin).                              |
| &#x1f512; `client.Projects.unlinkUser(id, userId, bodyParams = {}, queryParams = {})`                                           | Unlinks an active user from a project (aka. removing existing project admin).                   |
| *[ProjectLinks](https://presentator.io/docs/project-links)*                                                                     |                                                                                                 |
| &#x1f512; `client.ProjectLinks.getList(page = 1, perPage = 20, queryParams = {})`                                               | Returns paginated project links list.                                                           |
| &#x1f512; `client.ProjectLinks.getOne(id, queryParams = {})`                                                                    | Views single project link.                                                                      |
| &#x1f512; `client.ProjectLinks.create(bodyParams = {}, queryParams = {})`                                                       | Creates a new project link.                                                                     |
| &#x1f512; `client.ProjectLinks.update(id, bodyParams = {}, queryParams = {})`                                                   | Updates an existing project link.                                                               |
| &#x1f512; `client.ProjectLinks.delete(id, bodyParams = {}, queryParams = {})`                                                   | Deletes an existing project link.                                                               |
| &#x1f512; `client.ProjectLinks.share(id, bodyParams = {}, queryParams = {})`                                                    | Shares a project link with other users (including guests) by sending an email to them.          |
| &#x1f512; `client.ProjectLinks.getAccessed(page = 1, perPage = 20, queryParams = {})`                                           | Returns paginated list with accessed project links by the authorized user.                      |
| *[GuidelineSections](https://presentator.io/docs/guideline-sections)*                                                           |                                                                                                 |
| &#x1f512; `client.GuidelineSections.getList(page = 1, perPage = 20, queryParams = {})`                                          | Returns paginated guideline sections list.                                                      |
| &#x1f512; `client.GuidelineSections.getOne(id, queryParams = {})`                                                               | Views single guideline section.                                                                 |
| &#x1f512; `client.GuidelineSections.create(bodyParams = {}, queryParams = {})`                                                  | Creates a new guideline section.                                                                |
| &#x1f512; `client.GuidelineSections.update(id, bodyParams = {}, queryParams = {})`                                              | Updates an existing guideline section.                                                          |
| &#x1f512; `client.GuidelineSections.delete(id, bodyParams = {}, queryParams = {})`                                              | Deletes an existing guideline section.                                                          |
| *[GuidelineAssets](https://presentator.io/docs/guideline-assets)*                                                               |                                                                                                 |
| &#x1f512; `client.GuidelineAssets.getList(page = 1, perPage = 20, queryParams = {})`                                            | Returns paginated guideline assets list.                                                        |
| &#x1f512; `client.GuidelineAssets.getOne(id, queryParams = {})`                                                                 | Views single guideline asset.                                                                   |
| &#x1f512; `client.GuidelineAssets.create(bodyParams = {}, queryParams = {})`                                                    | Creates a new guideline asset.                                                                  |
| &#x1f512; `client.GuidelineAssets.update(id, bodyParams = {}, queryParams = {})`                                                | Updates an existing guideline asset.                                                            |
| &#x1f512; `client.GuidelineAssets.delete(id, bodyParams = {}, queryParams = {})`                                                | Deletes an existing guideline asset.                                                            |
| *[Prototypes](https://presentator.io/docs/prototypes)*                                                                          |                                                                                                 |
| &#x1f512; `client.Prototypes.getList(page = 1, perPage = 20, queryParams = {})`                                                 | Returns paginated prototypes list.                                                              |
| &#x1f512; `client.Prototypes.getOne(id, queryParams = {})`                                                                      | Views single prototype.                                                                         |
| &#x1f512; `client.Prototypes.create(bodyParams = {}, queryParams = {})`                                                         | Creates a new prototype.                                                                        |
| &#x1f512; `client.Prototypes.update(id, bodyParams = {}, queryParams = {})`                                                     | Updates an existing prototype.                                                                  |
| &#x1f512; `client.Prototypes.duplicate(id, bodyParams = {}, queryParams = {})`                                                  | Duplicates an existing prototype with its screens, hotspot templates and hotspots.              |
| &#x1f512; `client.Prototypes.delete(id, bodyParams = {}, queryParams = {})`                                                     | Deletes an existing prototype.                                                                  |
| *[Screens](https://presentator.io/docs/screens)*                                                                                |                                                                                                 |
| &#x1f512; `client.Screens.getList(page = 1, perPage = 20, queryParams = {})`                                                    | Returns paginated screens list.                                                                 |
| &#x1f512; `client.Screens.getOne(id, queryParams = {})`                                                                         | Views single screen.                                                                            |
| &#x1f512; `client.Screens.create(bodyParams = {}, queryParams = {})`                                                            | Creates a new screen.                                                                           |
| &#x1f512; `client.Screens.update(id, bodyParams = {}, queryParams = {})`                                                        | Updates an existing screen.                                                                     |
| &#x1f512; `client.Screens.bulkUpdate(bodyParams = {}, queryParams = {})`                                                        | Bulk updates all screens within a single prototype.                                             |
| &#x1f512; `client.Screens.delete(id, bodyParams = {}, queryParams = {})`                                                        | Deletes an existing screen.                                                                     |
| *[Hotspots](https://presentator.io/docs/hotspots)*                                                                              |                                                                                                 |
| &#x1f512; `client.Hotspots.getList(page = 1, perPage = 20, queryParams = {})`                                                   | Returns paginated hotspots list.                                                                |
| &#x1f512; `client.Hotspots.getOne(id, queryParams = {})`                                                                        | Views single hotspot.                                                                           |
| &#x1f512; `client.Hotspots.create(bodyParams = {}, queryParams = {})`                                                           | Creates a new hotspot.                                                                          |
| &#x1f512; `client.Hotspots.update(id, bodyParams = {}, queryParams = {})`                                                       | Updates an existing hotspot.                                                                    |
| &#x1f512; `client.Hotspots.delete(id, bodyParams = {}, queryParams = {})`                                                       | Deletes an existing hotspot.                                                                    |
| *[HotspotTemplates](https://presentator.io/docs/hotspot-templates)*                                                             |                                                                                                 |
| &#x1f512; `client.HotspotTemplates.getList(page = 1, perPage = 20, queryParams = {})`                                           | Returns paginated hotspot templates list.                                                       |
| &#x1f512; `client.HotspotTemplates.getOne(id, queryParams = {})`                                                                | Views single hotspot template.                                                                  |
| &#x1f512; `client.HotspotTemplates.create(bodyParams = {}, queryParams = {})`                                                   | Creates a new hotspot template.                                                                 |
| &#x1f512; `client.HotspotTemplates.update(id, bodyParams = {}, queryParams = {})`                                               | Updates an existing hotspot template.                                                           |
| &#x1f512; `client.HotspotTemplates.delete(id, bodyParams = {}, queryParams = {})`                                               | Deletes an existing hotspot template.                                                           |
| &#x1f512; `client.HotspotTemplates.getScreensList(id, queryParams = {})`                                                        | Returns list with all linked hotspot screen models.                                             |
| &#x1f512; `client.HotspotTemplates.linkScreen(id, screenId, bodyParams = {}, queryParams = {})`                                 | Links a single screen to a hotspot template.                                                    |
| &#x1f512; `client.HotspotTemplates.unlinkScreen(id, screenId, bodyParams = {}, queryParams = {})`                               | Unlinks a single screen from a hotspot template.                                                |
| *[ScreenComments](https://presentator.io/docs/screen-comments)*                                                                 |                                                                                                 |
| &#x1f512; `client.ScreenComments.getList(page = 1, perPage = 20, queryParams = {})`                                             | Returns paginated screen comments list.                                                         |
| &#x1f512; `client.ScreenComments.getOne(id, queryParams = {})`                                                                  | Views single screen comment.                                                                    |
| &#x1f512; `client.ScreenComments.create(bodyParams = {}, queryParams = {})`                                                     | Creates a new screen comment.                                                                   |
| &#x1f512; `client.ScreenComments.update(id, bodyParams = {}, queryParams = {})`                                                 | Updates an existing screen comment.                                                             |
| &#x1f512; `client.ScreenComments.delete(id, bodyParams = {}, queryParams = {})`                                                 | Deletes an existing screen comment.                                                             |
| &#x1f512; `client.ScreenComments.getUnread(queryParams = {})`                                                                   | Returns all unread screen comments for the authorized user (with eager loader metaData).        |
| &#x1f512; `client.ScreenComments.read(id, bodyParams = {}, queryParams = {})`                                                   | Marks a single screen comment as read for the authorized user.                                  |


## Development
```bash
# build and minify for production
npm run build

# run unit tests
npm test
```
