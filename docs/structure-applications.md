Presentator - Applications
======================================================================

The project has three applications: `api`, `app` and `console`.

Api is used to implement REST API support.

App is the main application that handles login, register, user administration and such functionality.

Console is typically used for cron jobs and low-level server management. Also it's used during application deployment and handles migrations and assets.

There is also a `common` directory that contains files used by more than one application. For example, `User` model.

Both `api` and `app` are web applications and both contain the web directory. That's the webroot you should point your web server to.

Each application has its own namespace and [alias](structure-path-aliases) corresponding to its name (_for the `app` the alias is `@main`_). Same applies to the common directory.
