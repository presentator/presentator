Presentator - Directory structure
======================================================================

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
docs/                    contains project doc files
environments/            contains environment-based overrides
*vendor/                 contains dependent 3rd-party packages
*node_modules/           contains node js modules like Grunt, node-sass, etc.
```

The root directory contains the following subdirectories:

- `api`          - api web application
- `app`          - app web application
- `common`       - files common to all applications
- `console`      - console application
- `environments` - environment configs

The root directory contains the following set of files.

- `.gitignore`       - contains a list of directories ignored by git
- `composer.json`    - Composer config
- `package.json`     - NPM config
- `Gruntfile.js`     - Grunt config
- `codeception.yml`  - global codeception file to run tests from all apps
- `init`             - application initialization script
- `init.bat`         - same for Windows
- `LICENSE.md`       - project license info
- `README.md`        - project doc start point
- `requirements.php` - Yii requirements checker
- *`yii`             - console application bootstrap
- *`yii.bat`         - same for Windows
- *`yii_test`        - test application bootstrap
- *`yii_test.bat`    - same for Windows
