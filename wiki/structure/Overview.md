The project has three application components: `api`, `app` and `console`.

Api is used to implement REST API support.

App is the main application that handles login, register, user administration and such functionality.

Console is typically used for cron jobs and low-level server management. Also it's used during application deployment and handles migrations and assets.

There is also a `common` directory that contains files used by more than one application. For example, `User` model.

Both `api` and `app` are web applications and both contain the web directory. That's the webroot you should point your web server to.

Each application has its own namespace and [alias](#project-defined-path-aliases) corresponding to its name (_for the `app` the alias is `@main`_). Same applies to the common directory.


### Project structure

```
api
    config/              contains api configurations
    controllers/         contains Web controller classes
    models/              contains api-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for api application
    web/                 contains the entry script and Web resources
app
    assets/              contains application assets such as JavaScript and CSS
    config/              contains app configurations
    controllers/         contains Web controller classes
    models/              contains app-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for app application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both app and api
    tests/               contains tests for common classes
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
docker/                  contains docker development environment settings and helper scripts
environments/            contains environment-based overrides
*vendor/                 contains dependent 3rd-party packages
*node_modules/           contains node js modules like Grunt, node-sass, etc.
```

The root directory contains the following subdirectories:

- `api`          - api web application
- `app`          - app web application
- `common`       - files common to all applications
- `console`      - console application
- `environments` - environment configurations

The root directory contains the following set of files.

- `.gitignore`         - contains a list of directories ignored by git
- `composer.json`      - Composer config
- `package.json`       - NPM config
- `Gruntfile.js`       - Grunt config
- `codeception.yml`    - global codeception file to run tests from all apps
- `docker-compose.yml` - docker development application services configurations 
- `init`               - application initialization script
- `init.bat`           - same for Windows
- `LICENSE.md`         - project license info
- `README.md`          - project doc start point
- `requirements.php`   - Yii requirements checker
- *`yii`               - console application bootstrap
- *`yii.bat`           - same for Windows
- *`yii_test`          - test application bootstrap
- *`yii_test.bat`      - same for Windows


### Project defined path aliases:

Defined in `common/config/bootstrap.php`.

- `@console`  - console directory
- `@common`   - common directory
- `@api`      - api web application directory
- `@main`     - main web application directory (**NB!** `@app` is reserved)
- `@mainWeb`  - main web application directory


### Yii 2 default path aliases:

Defined in the application init constructor.

- `@yii`      - framework directory
- `@app`      - base path of currently running application
- `@runtime`  - runtime directory of currently running web application
- `@vendor`   - Composer vendor directory
- `@bower`    - vendor directory that contains the [bower packages](http://bowerio/)
- `@web`      - base URL of currently running web application
- `@webroot`  - web root directory of currently running web application
