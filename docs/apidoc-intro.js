/**
 * @apiDefine User User access only
 * You have to set the user authorization token through the <code>X-Access-Token</code> header.
 */

/**
 *
 * @apiDefine 204
 * @apiSuccessExample {json} 204 Success response (example):
 * null
 */

/**
 *
 * @apiDefine 401
 * @apiErrorExample {json} 401 Unauthorized (example):
 * {
 *   "message": "Your request was made with invalid credentials.",
 *   "errors": []
 * }
 */

/**
 * @apiDefine 404
 * @apiErrorExample {json} 404 Not Found (example):
 * {
 *   "message": "The item you are looking for does not exist or is temporary unavailable.",
 *   "errors": []
 * }
 */

/**
 * @api {hide} off
 * @apiName HideBlock
 * @apiSampleRequest off
 * @apiGroup Intro
 * @apiDescription
Welcome to Presentator.io API docs.

The API resources are separated in 5 groups:

- **Previews** - Services related to the ProjectPreview model schema.

- **Users** - Services related to the User model schema.

- **Projects** - Services related to the Project model schema.

- **Versions** - Services related to the Version model schema.

- **Screens** - Services related to the Screen model schema.

- **Screen comments** - Services related to the ScreenComment model schema.

Each API resource could be localized by prefixing the result
with the appropriate language code, eg. `api.presentator.io/bg/...`.

The available language codes are: `bg`, `en`, `pt-br`, `pl`.
 */

 /**
  * @api {hide} off
  * 01. "envelope" parameter
  * @apiName envelopeParam
  * @apiGroup Intro
  * @apiSampleRequest off
  * @apiDeScription
 Enables you to output some additional data (like status code, pagination info, etc.) to the response.
 ```
 ?envelope=true
 ```

 Sample output:
 <pre class="prettyprint language-json">
 {"status":200,"headers":{"X-Access-Token":"example.access.token"},"response":{...}}
 </pre>
  */

 /**
  * @api {hide} off
  * 02. "fields" and "expand" parameters
  * @apiName fieldsExpandParams
  * @apiGroup Intro
  * @apiSampleRequest off
  * @apiDeScription

 - **fields**

     Used to filter the returned attributes. For example the following
     will output only the "id" and the "title" of each returned service main model:
     ```
     ?fields=id,title
     ```
     > **NB!** For more info about the allowed attributes on which the `fields` param could apply,
     check the description of the specific service.
     > By default - all main model's (first level object) attributes.

 - **expand**

     Load additional related resources to the main model on demand.
     ```
     ?expand=tags
     ```
     > **NB!** Check the description of the specific service for more info about the
     allowed relations that `expand` could resolve.
     > For convenience, most of the services automatically prefetch some of the commonly used rels.
  */

 /**
  * @api {hide} off
  * 03. Listing parameters
  * @apiName introListingParams
  * @apiGroup Intro
  * @apiSampleRequest off
  * @apiDeScription

 Most of the *listing services* (that returns array with objects) support the following
 GET parameters:

 - **per-page**

     Specify the number of the returned results
     (often used in combination with `page` param to create pagination).
     ```
     ?per-page=10
     ```

 - **page**

     Set the current page for paginated results.
     ```
     ?page=1
     ```

     If pagination is applied, will add to the response the following helper headers:

     `X-Pagination-Total-Count`  (total items)

     `X-Pagination-Page-Count`   (total pages)

     `X-Pagination-Per-Page`     (items per page)

     `X-Pagination-Current-Page` (current page number)

 - **sort**

     Define the order of the returned results. Use `-` / `+` (or nothing) in front of the attribute
     for DESC/ASC order
     ```
     ?sort=-created_at,+first_name,last_name
     ```
     > **NB!** For more info about the allowed attributes on which the `sort` param could apply,
     check the description of the specific service.

 - **q**
     ```
     ?q=term(sample-term1),id(1|!2|3)
     ```
     > **NB!** For more info about the allowed attributes on which the `q` param could apply,
     check the description of the specific service.
  */

/**
 * @api {hide} off
 * 06. Constants
 * @apiName introModelsConstants
 * @apiGroup Intro
 * @apiSampleRequest off
 * @apiDeScription
## Global (if nothing else is specified)
<table>
    <tr>
        <th style="width: 32%">Field</th>
        <th style="width: 32%">Value</th>
        <th style="width: 32%">Description</th>
    </tr>
    <tr>
        <td style="width: 32%" class="code">status</td>
        <td style="width: 32%" class="code">0</td>
        <td style="width: 32%"><p>Inactive</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">status</td>
        <td style="width: 32%" class="code">1</td>
        <td style="width: 32%"><p>Active</p></td>
    </tr>
</table>

## Project model
<table>
    <tr>
        <th style="width: 32%">Field</th>
        <th style="width: 32%">Value</th>
        <th style="width: 32%">Description</th>
    </tr>
    <tr>
        <td style="width: 32%" class="code">type</td>
        <td style="width: 32%" class="code">1</td>
        <td style="width: 32%"><p>Desktop</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">type</td>
        <td style="width: 32%" class="code">2</td>
        <td style="width: 32%"><p>Tablet</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">type</td>
        <td style="width: 32%" class="code">3</td>
        <td style="width: 32%"><p>Mobile</p></td>
    </tr>
    <tr class="delimiter"><td colspan="3"></td></tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">21</td>
        <td style="width: 32%"><p>Tablet screen - <code>[768, 1024]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">22</td>
        <td style="width: 32%"><p>Tablet screen - <code>[1024, 768]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">23</td>
        <td style="width: 32%"><p>Tablet screen - <code>[800, 1200]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">24</td>
        <td style="width: 32%"><p>Tablet screen - <code>[1200, 800]</code></p></td>
    </tr>
    <tr class="delimiter"><td colspan="3"></td></tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">31</td>
        <td style="width: 32%"><p>Mobile screen - <code>[320, 480]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">32</td>
        <td style="width: 32%"><p>Mobile screen - <code>[480, 320]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">33</td>
        <td style="width: 32%"><p>Mobile screen - <code>[375, 667]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">34</td>
        <td style="width: 32%"><p>Mobile screen - <code>[667, 375]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">35</td>
        <td style="width: 32%"><p>Mobile screen - <code>[412, 732]</code></p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">subtype</td>
        <td style="width: 32%" class="code">36</td>
        <td style="width: 32%"><p>Mobile screen - <code>[732, 712]</code></p></td>
    </tr>
</table>

## ProjectPreview model
<table>
    <tr>
        <th style="width: 32%">Field</th>
        <th style="width: 32%">Value</th>
        <th style="width: 32%">Description</th>
    </tr>
    <tr>
        <td style="width: 32%" class="code">type</td>
        <td style="width: 32%" class="code">1</td>
        <td style="width: 32%"><p>View only</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">type</td>
        <td style="width: 32%" class="code">2</td>
        <td style="width: 32%"><p>View and comment</p></td>
    </tr>
</table>

## Screen model
<table>
    <tr>
        <th style="width: 32%">Field</th>
        <th style="width: 32%">Value</th>
        <th style="width: 32%">Description</th>
    </tr>
    <tr>
        <td style="width: 32%" class="code">alignment</td>
        <td style="width: 32%" class="code">1</td>
        <td style="width: 32%"><p>Left</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">alignment</td>
        <td style="width: 32%" class="code">2</td>
        <td style="width: 32%"><p>Center</p></td>
    </tr>
    <tr>
        <td style="width: 32%" class="code">alignment</td>
        <td style="width: 32%" class="code">3</td>
        <td style="width: 32%"><p>Right</p></td>
    </tr>
</table>
 */
