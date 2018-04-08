Presentator - Installation
======================================================================

The project is designed to work in a team development environment.
It supports deploying the application in different environments (`prod`, `dev`).

- [Requirements](#requirements)
- [Installation](#installation)
- [Regular/Super User](#regularsuper-user)
- [Cron Jobs](#cron-jobs)
- [Updating app assets](#updating-app-assets)


## Requirements
- Apache HTTP server (_for Ngnix need to manually setup a redict rule to index.php_)

- _PHP 5.6+_ with the following extensions:
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
    GD
    ```

- DB - _MySQL v5.5+_ or _MariaDb 10+_ (recommended)

> From the project root directory run the console command `php requirements.php` for more detail check.


## Installation

#### For Production (GUI Installer)

1. Create a new database (with `utf8_general_ci` or `utf8_unicode_ci` collation).

2. Download the latest build from https://presentator.io/downloads/latest and extract it to your server directory.

    > For security reasons, if you are using a shared hosting service it is recommended to extract the archvie files **outside** from your default **public_html(www)** directory!

3. Setup your domain (or create a new subdomain) and point it to the main application web directory, eg. `/path/to/presentator/app/web`.

    > Optional: if you are intending to make use of the API of the platform, create a subdomain and point it to the api application web directory - `/path/to/presentator/api/web`.

4. Navigate to `https://my-site.example/install` and follow the instructions of the graphic installer.

![GUI Installer](installer.png)

After the installer has successfully completed you should be able to access and start using the platform at `https://my-site.example`.

> Don't worry, you can always change later the application configurations (db, mailer, etc.) by editting `/path/to/presentator/common/config/main-local.php` and `/path/to/presentator/common/config/params-local.php`


#### For Development (Manual)

> To install and setup the application components you will need [Composer](https://getcomposer.org/).

1. Clone the GitHub repository (or download and extract the archive).
    ```bash
    git clone https://github.com/ganigeorgiev/presentator.git
    ```

2. Setup a vhost/server address for the **app**, eg. `http://app.presentator.local/` and point it to `/app/web`.

3. Setup a vhost/server address for the **api**, eg. `http://api.presentator.local/` and point it to `/api/web`.

4. Open a console terminal and execute:
    ```bash
    # navigate to project root dir
    cd /path/to/presentator

    # install vendor dependencies
    composer install

    # execute the init command and select the appropriate environment
    php init
    ```

5. Create a new database and adjust the database and mailer components settings in `common/config/main-local.php` accordingly.

6. Adjust the applications required parameters in `common/config/params-local.php`.

7. Open a console terminal and apply migrations.
    ```bash
    php /path/to/project/yii migrate
    ```

That is! Now you should be able to access the previously created vhosts.


## Regular/Super User

By default all registered users has Regular User access rights (_access to only their own account and projects_).
For easier system administration you can set Super User access rights (_access to all system accounts and projects_) to one or more registered users (**available since v1.7+**).
Change the user's `type` column manually in your DB (_0 - Regular, 1 - Super_) or run one of the following console commands:

```bash
# sets Super User access
php yii users/super test@example.com

# sets Regular User access
php yii users/regular test@example.com
```


## Cron Jobs

To optimize the performance of the service you could schedule the following cron jobs:

- **Mails Queue processing (v1.3+)**

    First, make sure to enable `useMailQueue` parameter in `common/config/params-local.php`
    ```php
    return [
        'useMailQueue'   => true,
        'purgeSentMails' => true, // whether to delete successfully processed MailQueue records
        // ...
    ]
    ```
    Setup your crontab to run the `php yii mails/process` console command, eg.:
    ```bash
    # process 50 mails at every 5 minutes
    */5 * * * * php /path/to/project/yii mails/process 50
    ```
    > Check `console\controllers\MailsController::actionProcess()` for all available command arguments.

## Updating app assets

To be able to autogenerate api doc, change and minify app assets, first you have to install `npm` and then run `npm install`.

After that you should be able to start [Grunt](http://gruntjs.com/getting-started) with the `grunt` command and just wait all tasks to complete.

> Check Gruntfile.js for all available tasks and options.
