Presentator v2 REST API [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)
======================================================================

Presentator v2 REST API server implementation, written in PHP and based on [Yii2](https://www.yiiframework.com/).

**Detailed API reference could be found here - [https://presentator.io/docs](https://presentator.io/docs).**

- [Requirements](#requirements)
- [Installation](#installation)
- [Development](#development)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Requirements

- Apache/Nginx HTTP server

- SQL database (MySQL/MariadDB/PostgreSQL)

    > For MySQL up to 5.6 and MariaDB up to 10.1 you may need to set `innodb_large_prefix=1` and `innodb_default_row_format=dynamic` to prevent migration errors (see [#104](https://github.com/presentator/presentator/issues/104)).

- PHP 7.1+ with the following extensions:

    ```
    Reflection
    PCRE
    SPL
    MBString
    OpenSSL
    Intl
    ICU version
    Fileinfo
    DOM extensions
    GD or Imagick
    ```

    For more detailed check, run `php requirements.php` from the application root directory.

    In addition, here are some recommended `php.ini` configuration settings:
    ```
    post_max_size       = 64M
    upload_max_filesize = 64M
    max_execution_time  = 60
    memory_limit        = 256M
    ```


## Installation

Before getting started make sure that you have checked the project requirements and installed [Composer](https://getcomposer.org/).

1. Clone or download the repo.

    > **For security reasons, if you are using a shared hosting service it is recommended to place the project files outside from your default public_html(www) directory!**

2. Setup a vhost/server address (eg. `http://api.presentator.local/`) and point it to `web/`.

    > By default a generic `.htaccess` file will be created for you after initialization. If you are using nginx, you could check the following [sample configuration](https://github.com/presentator/presentator/issues/120#issuecomment-539844456).

3. Run the following commands:

    ```bash
    # navigate to the project root dir
    cd /path/to/project

    # install vendor dependencies
    composer install

    # execute the init command and select the appropriate environment:
    # dev     - for development
    # prod    - for production
    # starter - this is used only for the the starter project setup (https://github.com/presentator/presentator-starter)
    php init
    ```

4. Create a new database (with `utf8mb4_unicode_ci` collation) and adjust the db, mailer and other component configurations in `config/base-local.php` accordingly.

    > **All available app components with their default values could be found in `config/base.php`.**

5. Adjust the application parameters in `config/params-local.php.`.

    > **All available app parameters with their default values could be found in `config/params.php`.**

6. Apply DB migrations.

    ```bash
    php /path/to/project/yii migrate
    ```

7. (optional) Setup a cron task to process unread screen comments:

    ```bash
    # Every 30 minutes processes all unread screen comments and sends an email to the related users.
    */30 * * * * php /path/to/project/yii mails/process-comments
    ```

**That's it!** You should be able to make HTTP requests to the previously defined server address.

Additional console commands you may find useful:

```bash
# set Super User access rights to a single User model
php /path/to/project/yii users/super test@example.com

# set Regular User access rights to a single User model
php /path/to/project/yii users/regular test@example.com

# regenerates all screen thumbs
php /path/to/project/yii screens/generate-thumbs
```


## Development

#### Running tests

Presentator uses [Codeception](https://codeception.com/) as its primary test framework.

Running tests require an additional database, which will be cleaned up between tests.
Create a new database and edit the db component settings in `config/test-local.php` and then run the following console commands:

```bash
# apply db migrations for the test database
php path/to/project/yii_test migrate

# build the test suites
/path/to/project/vendor/bin/codecept build

# start all application tests
/path/to/project/vendor/bin/codecept run
```

> Currently only functional tests are available.

#### Conventions

The project makes use of the following conventions:

- *(PHP)* Each class must follow the accepted [PSR standards](https://www.php-fig.org/psr/#accepted).
- *(PHP)* Each class method should have comment block tags based on [PHPDoc](https://docs.phpdoc.org/references/phpdoc/index.html) (method description is optional).
- *(DB)* Use InnoDB table engine.
- *(DB)* Use `utf8mb4_unicode_ci` or `utf8_unicode_ci` collation.
- *(DB)* Table names must match with the corresponding AR model class name (eg. `UserProjectRel`).
- *(DB)* Table columns must be in camelCase format (eg. `passwordResetToken`)
- *(DB)* Each database change must be applied via Yii migrations.
- *(DB)* Whenever is possible add named foreign keys and indexes in the following format `fk_{FROM_TABLE}_to_{TO_TABLE}` and `idx_{TABLE}_{COLUMN(S)}` (eg. `fk_ProjectLink_to_Project`, `idx_ProjectLink_slug`)

#### DB schema

![erd](https://i.imgur.com/yjMo9RO.png)
