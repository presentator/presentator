define({ "api": [
  {
    "type": "hide",
    "url": "off",
    "title": "",
    "name": "HideBlock",
    "group": "Intro",
    "description": "<p>Welcome to Presentator API docs.</p> <p>The API resources are separated in 5 groups:</p> <ul> <li> <p><strong>Previews</strong> - Services related to the ProjectPreview model schema.</p> </li> <li> <p><strong>Users</strong> - Services related to the User model schema.</p> </li> <li> <p><strong>Projects</strong> - Services related to the Project model schema.</p> </li> <li> <p><strong>Versions</strong> - Services related to the Version model schema.</p> </li> <li> <p><strong>Screens</strong> - Services related to the Screen model schema.</p> </li> <li> <p><strong>Screen comments</strong> - Services related to the ScreenComment model schema.</p> </li> </ul> <p><em>The API is still in very early development and therefore if you have any feature suggestions, please report them at the <a href=\"https://github.com/ganigeorgiev/presentator/issues\">project GitHub issues page</a>.</em></p>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "hide",
    "url": "off",
    "title": "02. \"envelope\" parameter",
    "name": "envelopeParam",
    "group": "Intro",
    "description": "<p>Enables you to output some additional data (like status code, pagination info, etc.) to the response.</p> <pre><code>?envelope=true </code></pre> <p>Sample output:</p> <pre class=\"prettyprint language-json\"> {\"status\":200,\"headers\":{\"X-Access-Token\":\"example.access.token\"},\"response\":{...}} </pre>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "hide",
    "url": "off",
    "title": "03. \"fields\" and \"expand\" parameters",
    "name": "fieldsExpandParams",
    "group": "Intro",
    "description": "<ul> <li> <p><strong>fields</strong></p> <p>Used to filter the returned attributes. For example the following will output only the &quot;id&quot; and the &quot;title&quot; of each returned service main model:</p> <pre><code>?fields=id,title </code></pre> <blockquote> <p><strong>NB!</strong> For more info about the allowed attributes on which the <code>fields</code> param could apply, check the description of the specific service. By default - all main model's (first level object) attributes.</p> </blockquote> </li> <li> <p><strong>expand</strong></p> <p>Load additional related resources to the main model on demand.</p> <pre><code>?expand=settings </code></pre> <blockquote> <p><strong>NB!</strong> Check the description of the specific service for more info about the allowed relations that <code>expand</code> could resolve. For convenience, most of the services automatically prefetch some of the commonly used rels.</p> </blockquote> </li> </ul>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "hide",
    "url": "off",
    "title": "04. Listing parameters",
    "name": "introListingParams",
    "group": "Intro",
    "description": "<p>Most of the <em>listing services</em> (that returns array with objects) support the following GET parameters:</p> <ul> <li> <p><strong>per-page</strong> Specify the number of the returned results (often used in combination with <code>page</code> param to create pagination).</p> <pre><code>?per-page=10 </code></pre> </li> <li> <p><strong>page</strong> Set the current page for paginated results.</p> <pre><code>?page=1 </code></pre> <p>If pagination is applied, will add to the response the following helper headers:</p> <p><code>X-Pagination-Total-Count</code>  (total items)</p> <p><code>X-Pagination-Page-Count</code>   (total pages)</p> <p><code>X-Pagination-Per-Page</code>     (items per page)</p> <p><code>X-Pagination-Current-Page</code> (current page number)</p> </li> <li> <p><strong>sort</strong> Define the order of the returned results. Use <code>-</code> / <code>+</code> (or nothing) in front of the attribute for DESC/ASC order</p> <pre><code>?sort=-created_at,+first_name,last_name </code></pre> <blockquote> <p><strong>NB!</strong> For more info about the allowed attributes on which the <code>sort</code> param could apply, check the description of the specific service.</p> </blockquote> </li> </ul>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "hide",
    "url": "off",
    "title": "05. Constants",
    "name": "introModelsConstants",
    "group": "Intro",
    "description": "<h2>Global (if nothing else is specified)</h2> <table>     <tr>         <th style=\"width: 32%\">Field</th>         <th style=\"width: 32%\">Value</th>         <th style=\"width: 32%\">Description</th>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">status</td>         <td style=\"width: 32%\" class=\"code\">0</td>         <td style=\"width: 32%\"><p>Inactive</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">status</td>         <td style=\"width: 32%\" class=\"code\">1</td>         <td style=\"width: 32%\"><p>Active</p></td>     </tr> </table> <h2>Project model</h2> <table>     <tr>         <th style=\"width: 32%\">Field</th>         <th style=\"width: 32%\">Value</th>         <th style=\"width: 32%\">Description</th>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">type</td>         <td style=\"width: 32%\" class=\"code\">1</td>         <td style=\"width: 32%\"><p>Desktop</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">type</td>         <td style=\"width: 32%\" class=\"code\">2</td>         <td style=\"width: 32%\"><p>Tablet</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">type</td>         <td style=\"width: 32%\" class=\"code\">3</td>         <td style=\"width: 32%\"><p>Mobile</p></td>     </tr>     <tr class=\"delimiter\"><td colspan=\"3\"></td></tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">21</td>         <td style=\"width: 32%\"><p>Tablet screen - <code>[768, 1024]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">22</td>         <td style=\"width: 32%\"><p>Tablet screen - <code>[1024, 768]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">23</td>         <td style=\"width: 32%\"><p>Tablet screen - <code>[800, 1200]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">24</td>         <td style=\"width: 32%\"><p>Tablet screen - <code>[1200, 800]</code></p></td>     </tr>     <tr class=\"delimiter\"><td colspan=\"3\"></td></tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">31</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[320, 480]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">32</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[480, 320]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">33</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[375, 667]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">34</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[667, 375]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">35</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[412, 732]</code></p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">subtype</td>         <td style=\"width: 32%\" class=\"code\">36</td>         <td style=\"width: 32%\"><p>Mobile screen - <code>[732, 712]</code></p></td>     </tr>     <tr class=\"delimiter\"><td colspan=\"3\"></td></tr>     <tr>         <td style=\"width: 32%\" class=\"code\">scaleFactor</td>         <td style=\"width: 32%\" class=\"code\">0</td>         <td style=\"width: 32%\"><p>Auto fit scale factor</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">scaleFactor</td>         <td style=\"width: 32%\" class=\"code\">1</td>         <td style=\"width: 32%\"><p>None/Default scale factor</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">scaleFactor</td>         <td style=\"width: 32%\" class=\"code\">2</td>         <td style=\"width: 32%\"><p>Retian display scale factor</p></td>     </tr> </table> <h2>ProjectPreview model</h2> <table>     <tr>         <th style=\"width: 32%\">Field</th>         <th style=\"width: 32%\">Value</th>         <th style=\"width: 32%\">Description</th>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">type</td>         <td style=\"width: 32%\" class=\"code\">1</td>         <td style=\"width: 32%\"><p>View only</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">type</td>         <td style=\"width: 32%\" class=\"code\">2</td>         <td style=\"width: 32%\"><p>View and comment</p></td>     </tr> </table> <h2>Screen model</h2> <table>     <tr>         <th style=\"width: 32%\">Field</th>         <th style=\"width: 32%\">Value</th>         <th style=\"width: 32%\">Description</th>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">alignment</td>         <td style=\"width: 32%\" class=\"code\">1</td>         <td style=\"width: 32%\"><p>Left</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">alignment</td>         <td style=\"width: 32%\" class=\"code\">2</td>         <td style=\"width: 32%\"><p>Center</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">alignment</td>         <td style=\"width: 32%\" class=\"code\">3</td>         <td style=\"width: 32%\"><p>Right</p></td>     </tr>     <tr class=\"delimiter\"><td colspan=\"3\"></td></tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">none</td>         <td style=\"width: 32%\"><p>No transition animation on hotspot click</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">fade</td>         <td style=\"width: 32%\"><p>Fade animation on hotspot click</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">slide-left</td>         <td style=\"width: 32%\"><p>Slide screen from left animation on hotspot click</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">slide-right</td>         <td style=\"width: 32%\"><p>Slide screen from right animation on hotspot click</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">slide-top</td>         <td style=\"width: 32%\"><p>Slide screen from top animation on hotspot click</p></td>     </tr>     <tr>         <td style=\"width: 32%\" class=\"code\">hotspot transition</td>         <td style=\"width: 32%\" class=\"code\">slide-bottom</td>         <td style=\"width: 32%\"><p>Slide screen from bottom animation on hotspot click</p></td>     </tr> </table>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "hide",
    "url": "off",
    "title": "01. Localizing response",
    "name": "localizations",
    "group": "Intro",
    "description": "<p>Each API response could be localized with the <code>lang</code> GET parameter:</p> <pre><code>?lang=bg </code></pre> <p>Currently available language codes are: <code>bg</code>, <code>de</code>, <code>en</code>, <code>es</code>, <code>fr</code>, <code>pl</code>, <code>pt-br</code>, <code>sq-al</code>.</p> <p>If the <code>lang</code> parameter is not set, the API will try to detect it via <strong>GeoIP</strong>.</p>",
    "version": "0.0.0",
    "filename": "api/web/apidoc-template/base/apidoc-intro.js",
    "groupTitle": "Intro"
  },
  {
    "type": "POST",
    "url": "/previews/:slug",
    "title": "02. Leave comment",
    "name": "leaveComment",
    "group": "Previews",
    "description": "<p>Leave a new comment to a specific project preview screen (the <code>slug</code> must relate to a <code>ProjectPreview</code> model with type <em>View and Comment</em>). Returns the new created comment.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "slug",
            "description": "<p>ProjectPreview model slug (<code>GET</code> parameter)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "password",
            "description": "<p>Project password (<code>GET</code> parameter)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>Comment message</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "from",
            "description": "<p>Sender email address</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "screenId",
            "description": "<p>Id of the screen to leave the comment at (<strong>optional</strong> for a reply comment)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "posX",
            "description": "<p>Left position of the comment target (<strong>optional</strong> for a reply comment)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "posY",
            "description": "<p>Top position of the comment target (<strong>optional</strong> for a reply comment)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "replyTo",
            "description": "<p>Id of the comment to reply</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 68\n  \"replyTo\": null,\n  \"screenId\": 157,\n  \"posX\": 100,\n  \"posY\": 145,\n  \"message\": \"Lorem ipsum dolor sit amet\",\n  \"from\": \"test123@presentator.io\",\n  \"createdAt\": 1490289032,\n  \"updatedAt\": 1490289032,\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": [\n    \"screenId\": \"Invalid screen ID.\"\n  ]\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"The project is password protected.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "403 Forbidden (example):",
          "content": "{\n  \"message\": \"You must provide a valid project password.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/PreviewsController.php",
    "groupTitle": "Previews",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/previews/:slug"
      }
    ]
  },
  {
    "type": "GET",
    "url": "/previews/:slug",
    "title": "01. Get project preview",
    "name": "projectPreview",
    "group": "Previews",
    "description": "<p>Returns a ProjectPreview model with its related project.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "slug",
            "description": "<p>ProjectPreview model slug</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "password",
            "description": "<p>Project password (if has any)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 18,\n  \"projectId\": 9,\n  \"slug\": \"ckfaBI6X\",\n  \"type\": 2,\n  \"createdAt\": 1489904385,\n  \"updatedAt\": 1489904385,\n  \"project\": {\n    \"id\": 9,\n    \"title\": \"test\",\n    \"type\": 1,\n    \"subtype\": null,\n    \"createdAt\": 1489904385,\n    \"updatedAt\": 1490285838,\n    \"featured\": {\n      \"id\": 157,\n      \"versionId\": 21,\n      \"title\": \"attachment2\",\n      \"hotspots\": null,\n      \"order\": 1,\n      \"alignment\": 0,\n      \"background\": null,\n      \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n      \"createdAt\": 1489926572,\n      \"updatedAt\": 1489926572,\n      \"thumbs\": {\n        \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n        \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n      }\n    },\n    \"versions\": [\n      {\n        \"id\": 21,\n        \"projectId\": 9,\n        \"order\": 1,\n        \"createdAt\": 1489904385,\n        \"updatedAt\": 1489904385,\n        \"screens\": [\n          {\n            \"id\": 157,\n            \"versionId\": 21,\n            \"title\": \"attachment2\",\n            \"hotspots\": null,\n            \"order\": 1,\n            \"alignment\": 0,\n            \"background\": null,\n            \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n            \"createdAt\": 1489926572,\n            \"updatedAt\": 1489926572,\n            \"thumbs\": {\n              \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n              \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n            }\n          },\n          {\n            \"id\": 158,\n            \"versionId\": 21,\n            \"title\": \"attachment\",\n            \"hotspots\": null,\n            \"order\": 2,\n            \"alignment\": 0,\n            \"background\": null,\n            \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64.png\",\n            \"createdAt\": 1489926573,\n            \"updatedAt\": 1489926573,\n            \"thumbs\": {\n              \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_medium.png\",\n              \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_small.png\"\n            }\n          }\n        ]\n      }\n    ]\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"The project is password protected.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "403 Forbidden (example):",
          "content": "{\n  \"message\": \"You must provide a valid project password.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/PreviewsController.php",
    "groupTitle": "Previews",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/previews/:slug"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/projects",
    "title": "02. Create project",
    "name": "create",
    "group": "Projects",
    "description": "<p>Create a new <code>Project</code> model.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "title",
            "description": "<p>Project title</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "type",
            "description": "<p>Project type</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "subtype",
            "description": "<p>Project subtype (<strong>required</strong> only for projects with type <code>2 - tablet</code> or <code>3 - mobile</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "password",
            "description": "<p>Project password (if has any)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 11,\n  \"title\": \"My new project\",\n  \"type\": 1,\n  \"subtype\": null,\n  \"createdAt\": 1490296356,\n  \"updatedAt\": 1490296356,\n  \"featured\": null,\n  \"versions\": [\n    {\n      \"id\": 23,\n      \"projectId\": 11,\n      \"order\": 1,\n      \"createdAt\": 1490296359,\n      \"updatedAt\": 1490296359,\n      \"screens\": []\n    }\n  ],\n  \"previews\": [\n    {\n      \"id\": 1,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-1\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    },\n    {\n      \"id\": 2,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-2\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": {\n    \"title\": \"Title cannot be blank.\",\n    \"type\": \"Type is invalid.\"\n  }\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ProjectsController.php",
    "groupTitle": "Projects",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/projects"
      }
    ]
  },
  {
    "type": "DELETE",
    "url": "/projects/:id",
    "title": "05. Delete project",
    "name": "delete",
    "group": "Projects",
    "description": "<p>Delete an existing <code>Project</code> model owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Project id</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "api/controllers/ProjectsController.php",
    "groupTitle": "Projects",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/projects/:id"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "204 Success response (example):",
          "content": "null",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/projects",
    "title": "01. List projects",
    "name": "index",
    "group": "Projects",
    "description": "<p>Return list with projects owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "[\n  {\n    \"id\": 7,\n    \"title\": \"Test 1\",\n    \"type\": 1,\n    \"subtype\": null,\n    \"createdAt\": 1489904382,\n    \"updatedAt\": 1489904382,\n    \"featured\": null,\n    \"previews\": [\n      {\n        \"id\": 1,\n        \"projectId\": 7,\n        \"slug\": \"preview-slug-1\",\n        \"type\": 1,\n        \"createdAt\": 1524306495,\n        \"updatedAt\": 1524306495\n      },\n      {\n        \"id\": 2,\n        \"projectId\": 7,\n        \"slug\": \"preview-slug-2\",\n        \"type\": 1,\n        \"createdAt\": 1524306495,\n        \"updatedAt\": 1524306495\n      }\n    ]\n  },\n  {\n    \"id\": 9,\n    \"title\": \"Test 2\",\n    \"type\": 2,\n    \"subtype\": 21,\n    \"createdAt\": 1489904385,\n    \"updatedAt\": 1490286679,\n    \"featured\": {\n      \"id\": 157,\n      \"versionId\": 21,\n      \"title\": \"attachment2\",\n      \"hotspots\": null,\n      \"order\": 1,\n      \"alignment\": 0,\n      \"background\": null,\n      \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n      \"createdAt\": 1489926572,\n      \"updatedAt\": 1489926572,\n      \"thumbs\": {\n        \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n        \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n      }\n    },\n    \"previews\": [\n      {\n        \"id\": 1,\n        \"projectId\": 9,\n        \"slug\": \"preview-slug-1\",\n        \"type\": 1,\n        \"createdAt\": 1524306495,\n        \"updatedAt\": 1524306495\n      },\n      {\n        \"id\": 2,\n        \"projectId\": 9,\n        \"slug\": \"preview-slug-2\",\n        \"type\": 1,\n        \"createdAt\": 1524306495,\n        \"updatedAt\": 1524306495\n      }\n    ]\n  }\n]",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ProjectsController.php",
    "groupTitle": "Projects",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/projects"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "PUT",
    "url": "/projects/:id",
    "title": "04. Update project",
    "name": "update",
    "group": "Projects",
    "description": "<p>Update and return an existing <code>Project</code> model owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Project id (<code>GET</code> parameter)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "title",
            "description": "<p>Project title</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "type",
            "description": "<p>Project type</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "subtype",
            "description": "<p>Project subtype (<strong>required</strong> only for projects with type <code>2 - tablet</code> or <code>3 - mobile</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "password",
            "description": "<p>Project password (if has any)</p>"
          },
          {
            "group": "Parameter",
            "type": "Boolean",
            "optional": true,
            "field": "changePassword",
            "description": "<p>Set to <code>true</code> if you want to change/remove the project password</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 11,\n  \"title\": \"My new project\",\n  \"type\": 1,\n  \"subtype\": null,\n  \"createdAt\": 1490296356,\n  \"updatedAt\": 1490296356,\n  \"featured\": null,\n  \"versions\": [\n    {\n      \"id\": 23,\n      \"projectId\": 11,\n      \"order\": 1,\n      \"createdAt\": 1490296359,\n      \"updatedAt\": 1490296359,\n      \"screens\": []\n    }\n  ],\n  \"previews\": [\n    {\n      \"id\": 1,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-1\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    },\n    {\n      \"id\": 2,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-2\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": {\n    \"title\": \"Title cannot be blank.\",\n    \"type\": \"Type is invalid.\"\n  }\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ProjectsController.php",
    "groupTitle": "Projects",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/projects/:id"
      }
    ]
  },
  {
    "type": "GET",
    "url": "/projects/:id",
    "title": "03. View project",
    "name": "view",
    "group": "Projects",
    "description": "<p>Return an existing <code>Project</code> model owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Project id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"title\": \"My new project\",\n  \"type\": 2,\n  \"subtype\": 21,\n  \"createdAt\": 1490296356,\n  \"updatedAt\": 1490296356,\n  \"id\": 11,\n  \"featured\": {\n    \"id\": 151,\n    \"versionId\": 23,\n    \"title\": \"attachment2\",\n    \"hotspots\": null,\n    \"order\": 1,\n    \"alignment\": 0,\n    \"background\": null,\n    \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n    \"createdAt\": 1489926572,\n    \"updatedAt\": 1489926572,\n    \"thumbs\": {\n      \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n      \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n    }\n  },\n  \"versions\": [\n    {\n      \"id\": 23,\n      \"projectId\": 11,\n      \"order\": 1,\n      \"createdAt\": 1490296359,\n      \"updatedAt\": 1490296359,\n      \"screens\": [\n        {\n          \"id\": 151,\n          \"versionId\": 23,\n          \"title\": \"attachment2\",\n          \"hotspots\": null,\n          \"order\": 1,\n          \"alignment\": 0,\n          \"background\": null,\n          \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n          \"createdAt\": 1489926572,\n          \"updatedAt\": 1489926572,\n          \"thumbs\": {\n            \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n            \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n          }\n        }\n      ]\n    }\n  ],\n  \"previews\": [\n    {\n      \"id\": 1,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-1\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    },\n    {\n      \"id\": 2,\n      \"projectId\": 11,\n      \"slug\": \"preview-slug-2\",\n      \"type\": 1,\n      \"createdAt\": 1524306495,\n      \"updatedAt\": 1524306495\n    }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ProjectsController.php",
    "groupTitle": "Projects",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/projects/:id"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "POST",
    "url": "/comments",
    "title": "02. Create comment",
    "name": "create",
    "group": "Screen_comments",
    "description": "<p>Create and return a new <code>ScreenComment</code> model. The related comment screen must be from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>Comment message</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "replyTo",
            "description": "<p>Id of the comment to reply</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "screenId",
            "description": "<p>Id of the screen to leave the comment at (<strong>optional</strong> for a reply comment)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "posX",
            "description": "<p>Left position of the comment target (<strong>optional</strong> for a reply comment)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "posY",
            "description": "<p>Top position of the comment target (<strong>optional</strong> for a reply comment)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 68,\n  \"replyTo\": null,\n  \"screenId\": 159,\n  \"from\": \"test123@presentator.io\",\n  \"message\": \"Lorem ipsum dolor sit amet\",\n  \"isRead\": 0,\n  \"posX\": 100,\n  \"posY\": 145,\n  \"createdAt\": 1490289032,\n  \"updatedAt\": 1490289032\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": [\n    \"screenId\": \"Invalid screen ID.\"\n  ]\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreenCommentsController.php",
    "groupTitle": "Screen_comments",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/comments"
      }
    ]
  },
  {
    "type": "DELETE",
    "url": "/comments/:id",
    "title": "04. Delete comment",
    "name": "delete",
    "group": "Screen_comments",
    "description": "<p>Delete an existing <code>ScreenComment</code> model from a screen owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Comment id</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreenCommentsController.php",
    "groupTitle": "Screen_comments",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/comments/:id"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "204 Success response (example):",
          "content": "null",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/comments",
    "title": "01. List comments",
    "name": "index",
    "group": "Screen_comments",
    "description": "<p>Return list with comments from all screens owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "[\n  {\n    \"id\": 58,\n    \"replyTo\": null,\n    \"screenId\": 157,\n    \"from\": \"gani.georgiev@gmail.com\",\n    \"message\": \"asdasd\",\n    \"isRead\": 0,\n    \"posX\": 550,\n    \"posY\": 235,\n    \"createdAt\": 1489997571,\n    \"updatedAt\": 1489997571\n  },\n  {\n    \"id\": 59,\n    \"replyTo\": 58,\n    \"screenId\": 157,\n    \"from\": \"gani.georgiev@gmail.com\",\n    \"message\": \"asdasd\",\n    \"isRead\": 0,\n    \"posX\": 550,\n    \"posY\": 235,\n    \"createdAt\": 1489997660,\n    \"updatedAt\": 1489997660\n  },\n  {\n    \"id\": 68,\n    \"replyTo\": null,\n    \"screenId\": 159,\n    \"from\": \"test123@presentator.io\",\n    \"message\": \"Lorem ipsum dolor sit amet\",\n    \"isRead\": 0,\n    \"posX\": 100,\n    \"posY\": 145,\n    \"createdAt\": 1490289032,\n    \"updatedAt\": 1490289032\n  }\n]",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreenCommentsController.php",
    "groupTitle": "Screen_comments",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/comments"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/comments/:id",
    "title": "03. View comment",
    "name": "view",
    "group": "Screen_comments",
    "description": "<p>Return an existing <code>ScreenComment</code> model from a screen owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Comment id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 68,\n  \"replyTo\": null,\n  \"screenId\": 159,\n  \"from\": \"test123@presentator.io\",\n  \"message\": \"Lorem ipsum dolor sit amet\",\n  \"isRead\": 0,\n  \"posX\": 100,\n  \"posY\": 145,\n  \"createdAt\": 1490289032,\n  \"updatedAt\": 1490289032\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreenCommentsController.php",
    "groupTitle": "Screen_comments",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/comments/:id"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "POST",
    "url": "/screens",
    "title": "02. Upload screen",
    "name": "create",
    "group": "Screens",
    "description": "<p>Create and return a new <code>Screen</code> model.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "File",
            "optional": false,
            "field": "image",
            "description": "<p>Uploaded file</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "versionId",
            "description": "<p>Version id (must be from a project owned by the authenticated user)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "title",
            "description": "<p>Screen title</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "alignment",
            "description": "<p>Screen alignment</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "background",
            "description": "<p>Screen background HEX color code (eg. <code>#ffffff</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "Mixed",
            "optional": true,
            "field": "hotspots",
            "description": "<p>Screen hotspots as json encoded string or array in the following format: <code>{&quot;hostpot_id_1&quot;: {...}, &quot;hostpot_id_2&quot;: {...}}</code></p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.left",
            "description": "<p>Left (X) hotspot coordinate</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.top",
            "description": "<p>Left (Y) hotspot coordinate</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.width",
            "description": "<p>Hotspot width</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.height",
            "description": "<p>Hotspot height</p>"
          },
          {
            "group": "Parameter",
            "type": "Mixed",
            "optional": false,
            "field": "hotspots.link",
            "description": "<p>Hotspot link target - screen id or external url</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "hotspots.transition",
            "description": "<p>Hotspot transition effect (<code>none</code>, <code>fade</code>, <code>slide-left</code>, <code>slide-right</code>, <code>slide-top</code>, <code>slide-bottom</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "order",
            "description": "<p>Screen position within its version</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 161,\n  \"versionId\": 21,\n  \"title\": \"dashboard3\",\n  \"hotspots\": {\n    \"hotspot_1490302776820\": {\n      \"left\": 504,\n      \"top\": 204,\n      \"width\": 130,\n      \"height\": 106,\n      \"link\": 161,\n      \"transition\": \"none\"\n     }\n  },\n  \"order\": 3,\n  \"alignment\": 1,\n  \"background\": null,\n  \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png\",\n  \"createdAt\": 1489927288,\n  \"updatedAt\": 1489927288,\n  \"thumbs\": {\n    \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png\",\n    \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": [\n    \"title\": \"Invalid screen ID.\"\n  ]\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreensController.php",
    "groupTitle": "Screens",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/screens"
      }
    ]
  },
  {
    "type": "DELETE",
    "url": "/screens/:id",
    "title": "05. Delete screen",
    "name": "delete",
    "group": "Screens",
    "description": "<p>Delete an existing <code>Screen</code> model from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Screen id</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreensController.php",
    "groupTitle": "Screens",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/screens/:id"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "204 Success response (example):",
          "content": "null",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/screens",
    "title": "01. List screens",
    "name": "index",
    "group": "Screens",
    "description": "<p>Return list with screens from all projects owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "[\n  {\n    \"id\": 157,\n    \"versionId\": 21,\n    \"title\": \"attachment2\",\n    \"hotspots\": {\n      \"hotspot_1490302776820\": {\n        \"left\": 504,\n        \"top\": 204,\n        \"width\": 130,\n        \"height\": 106,\n        \"link\": 161,\n        \"transition\": \"fade\"\n       }\n    },\n    \"order\": 1,\n    \"alignment\": 2,\n    \"background\": null,\n    \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg\",\n    \"createdAt\": 1489926572,\n    \"updatedAt\": 1489926572,\n    \"thumbs\": {\n      \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg\",\n      \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg\"\n    }\n  },\n  {\n    \"id\": 158,\n    \"versionId\": 21,\n    \"title\": \"attachment\",\n    \"hotspots\": null,\n    \"order\": 2,\n    \"alignment\": 1,\n    \"background\": null,\n    \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64.png\",\n    \"createdAt\": 1489926573,\n    \"updatedAt\": 1489926573,\n    \"thumbs\": {\n      \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_medium.png\",\n      \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_small.png\"\n    }\n  }\n]",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreensController.php",
    "groupTitle": "Screens",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/screens"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "PUT",
    "url": "/screens/:id",
    "title": "04. Update screen",
    "name": "update",
    "group": "Screens",
    "description": "<p>Update and return an existing <code>Screen</code> model from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Id of the screen to update (<code>GET</code> parameter)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "versionId",
            "description": "<p>Version id (must be from a project owned by the authenticated user)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "title",
            "description": "<p>Screen title</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "alignment",
            "description": "<p>Screen alignment</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "background",
            "description": "<p>Screen background HEX color code (eg. <code>#ffffff</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "Mixed",
            "optional": true,
            "field": "hotspots",
            "description": "<p>Screen hotspots as json encoded string or array in the following format: <code>{&quot;hostpot_id_1&quot;: {...}, &quot;hostpot_id_2&quot;: {...}}</code></p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.left",
            "description": "<p>Left (X) hotspot coordinate</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.top",
            "description": "<p>Left (Y) hotspot coordinate</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.width",
            "description": "<p>Hotspot width</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "hotspots.height",
            "description": "<p>Hotspot height</p>"
          },
          {
            "group": "Parameter",
            "type": "Mixed",
            "optional": false,
            "field": "hotspots.link",
            "description": "<p>Hotspot link target - screen id or external url</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "hotspots.transition",
            "description": "<p>Hotspot transition effect (<code>none</code>, <code>fade</code>, <code>slide-left</code>, <code>slide-right</code>, <code>slide-top</code>, <code>slide-bottom</code>)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "order",
            "description": "<p>Screen position within its version</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 161,\n  \"versionId\": 21,\n  \"title\": \"New title\",\n  \"hotspots\": null,\n  \"order\": 3,\n  \"alignment\": 1,\n  \"background\": null,\n  \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png\",\n  \"createdAt\": 1489927288,\n  \"updatedAt\": 1489927288,\n  \"thumbs\": {\n    \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png\",\n    \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": [\n    \"title\": \"Title cannot be blank.\",\n    \"versionId\": \"Invalid version ID.\"\n  ]\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreensController.php",
    "groupTitle": "Screens",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/screens/:id"
      }
    ]
  },
  {
    "type": "GET",
    "url": "/screens/:id",
    "title": "03. View screen",
    "name": "viewzscdsa",
    "group": "Screens",
    "description": "<p>Return an existing <code>Screen</code> model from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Screen id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 161,\n  \"versionId\": 21,\n  \"title\": \"dashboard3\",\n  \"hotspots\": {\n    \"hotspot_1490302776820\": {\n      \"left\": 504,\n      \"top\": 204,\n      \"width\": 130,\n      \"height\": 106,\n      \"link\": 161\n     }\n  },\n  \"order\": 3,\n  \"alignment\": 1,\n  \"background\": '#ffffff',\n  \"imageUrl\": \"/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png\",\n  \"createdAt\": 1489927288,\n  \"updatedAt\": 1489927288,\n  \"thumbs\": {\n    \"medium\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png\",\n    \"small\": \"http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": [\n    \"title\": \"Title cannot be blank.\",\n    \"versionId\": \"Invalid version ID.\"\n  ]\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/ScreensController.php",
    "groupTitle": "Screens",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/screens/:id"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/users/login",
    "title": "01. Login",
    "name": "login",
    "group": "Users",
    "description": "<p>Login a specific User model and generate new authentication token (set via <code>X-Access-Token</code> response header).</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "email",
            "description": "<p>User email address</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>User password</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 1,\n  \"email\": \"test@presentator.io\",\n  \"firstName\": \"Lorem\",\n  \"lastName\": \"Ipsum\",\n  \"status\": 1,\n  \"type\": 0,\n  \"createdAt\": 1489244154,\n  \"updatedAt\": 1489244169,\n  \"avatar\": \"https://app.presentator.io/uploads/users/c8f636f067f89cc148621e728d9d4c2c/avatar.jpg\",\n  \"settings\": {\n    \"notifications\": true,\n    \"mentions\": true\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Invalid login credentials.\",\n  \"errors\": {\n    \"password\": \"Invalid password.\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/users/login"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/users/register",
    "title": "04. Register",
    "name": "register",
    "group": "Users",
    "description": "<p>Register and create a new <strong>Inactive</strong> <code>User</code> model. The new created user still need to verify its email.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "email",
            "description": "<p>User email</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>User password</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "passwordConfirm",
            "description": "<p>User password confirmation</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "firstName",
            "description": "<p>User first name</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "lastName",
            "description": "<p>User last name</p>"
          },
          {
            "group": "Parameter",
            "type": "Boolean",
            "optional": true,
            "field": "notifications",
            "description": "<p>User notifications setting for receiving emails when new comment is leaved (<code>true</code> by default)</p>"
          },
          {
            "group": "Parameter",
            "type": "File",
            "optional": true,
            "field": "avatar",
            "description": "<p>User avatar image</p>"
          }
        ]
      }
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": {\n    \"email\": \"Invalid email address.\",\n    \"password\": \"Password cannot be blank.\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/users/register"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "204 Success response (example):",
          "content": "null",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "PUT",
    "url": "/users/update",
    "title": "04. Update authenticated user",
    "name": "update",
    "group": "Users",
    "description": "<p>Updates an authenticated <code>User</code> model.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "firstName",
            "description": "<p>User first name</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "lastName",
            "description": "<p>User last name</p>"
          },
          {
            "group": "Parameter",
            "type": "Boolean",
            "optional": true,
            "field": "notifications",
            "description": "<p>User notifications setting for receiving emails on new leaved comment (<code>true</code> by default)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "oldPassword",
            "description": "<p>User old password (<strong>required</strong> on user password change)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "newPassword",
            "description": "<p>User new password (<strong>required</strong> on user password change)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "newPasswordConfirm",
            "description": "<p>User new password confirmation (<strong>required</strong> on user password change)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 1,\n  \"email\": \"test@presentator.io\",\n  \"firstName\": \"Lorem\",\n  \"lastName\": \"Ipsum\",\n  \"status\": 1,\n  \"type\": 0,\n  \"createdAt\": 1489244154,\n  \"updatedAt\": 1489244169,\n  \"avatar\": \"https://app.presentator.io/uploads/users/c8f636f067f89cc148621e728d9d4c2c/avatar.jpg\",\n  \"settings\": {\n    \"notifications\": true,\n    \"mentions\": true\n  }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": {\n    \"newPassword\": \"New Password cannot be blank.\",\n    \"newPasswordConfirm\": \"New Password Confirm cannot be blank.\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/users/update"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/versions",
    "title": "02. Create version",
    "name": "create",
    "group": "Versions",
    "description": "<p>Create and return a new <code>Version</code> model.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "projectId",
            "description": "<p>Id of a project owned by the authenticated user</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 25\n  \"projectId\": 7,\n  \"order\": 2,\n  \"createdAt\": 1490299034,\n  \"updatedAt\": 1490299034,\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "400 Bad Request (example):",
          "content": "{\n  \"message\": \"Oops, an error occurred while processing your request.\",\n  \"errors\": {\n    \"projectId\": \"Invalid project ID.\"\n  }\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/VersionsController.php",
    "groupTitle": "Versions",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/versions"
      }
    ]
  },
  {
    "type": "DELETE",
    "url": "/versions/:id",
    "title": "04. Delete version",
    "name": "delete",
    "group": "Versions",
    "description": "<p>Delete an existing <code>Version</code> model from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Version id</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "api/controllers/VersionsController.php",
    "groupTitle": "Versions",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/versions/:id"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "204 Success response (example):",
          "content": "null",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/versions",
    "title": "01. List versions",
    "name": "index",
    "group": "Versions",
    "description": "<p>Return list with versions from all projects owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "[\n  {\n    \"id\": 19,\n    \"projectId\": 7,\n    \"order\": 1,\n    \"createdAt\": 1489904382,\n    \"updatedAt\": 1489904382\n  },\n  {\n    \"id\": 20,\n    \"projectId\": 8,\n    \"order\": 1,\n    \"createdAt\": 1489904384,\n    \"updatedAt\": 1489904384\n  }\n]",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/VersionsController.php",
    "groupTitle": "Versions",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/versions"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "GET",
    "url": "/versions/:id",
    "title": "03. View version",
    "name": "view",
    "group": "Versions",
    "description": "<p>Return an existing <code>Version</code> model from a project owned by the authenticated user.</p>",
    "permission": [
      {
        "name": "User",
        "title": "User access only",
        "description": "<p>You have to set the user authorization token through the <code>X-Access-Token</code> header.</p>"
      }
    ],
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "X-Access-Token",
            "description": "<p>User authentication token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>Version id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "200 Success response (example):",
          "content": "{\n  \"id\": 25\n  \"projectId\": 7,\n  \"order\": 2,\n  \"createdAt\": 1490299034,\n  \"updatedAt\": 1490299034,\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "api/controllers/VersionsController.php",
    "groupTitle": "Versions",
    "sampleRequest": [
      {
        "url": "https://api.presentator.io/versions/:id"
      }
    ],
    "error": {
      "examples": [
        {
          "title": "404 Not Found (example):",
          "content": "{\n  \"message\": \"The item you are looking for does not exist or is temporary unavailable.\",\n  \"errors\": []\n}",
          "type": "json"
        },
        {
          "title": "401 Unauthorized (example):",
          "content": "{\n  \"message\": \"Your request was made with invalid credentials.\",\n  \"errors\": []\n}",
          "type": "json"
        }
      ]
    }
  }
] });
