Presentator - Installation
======================================================================

The project is designed to work in a team development environment.
It supports deploying the application in different environments.

## Requirements
The minimum server requirements to run the project are:

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

## Installation
To install and setup the application components you will need [Composer](https://getcomposer.org/).

1. Clone the GitHub repository (or download and extract the archive).
    ```
    git clone https://github.com/ganigeorgiev/presentator.git
    ```

2. Setup a vhost/server address for the **app**, eg. `http://app.presentator.dev/` and point it to `/app/web`.

3. Setup a vhost/server address for the **api**, eg. `http://api.presentator.dev/` and point it to `/api/web`.

4. Open a console terminal and execute:
    ```
    # navigate to project root dir
    cd /path/to/my/project

    # install vendor dependencies
    composer global require "fxp/composer-asset-plugin:^1.3.1"
    composer install

    # only for development
    npm install
    ```

    > For production `npm` command is optional but it is required if you want to change the API docs and app assets.

5. Execute the `init` command and select the appropriate environment.
    ```
    php /path/to/my/project/init
    ```

6. Adjust applications required params in `common/config/params-local.php`.

7. Create a new database and adjust the `components['db']` configuration in `common/config/main-local.php` accordingly.

8. Open a console terminal and apply migrations.
    ```
    php /path/to/my/project/yii migrate
    ```

That is! Now you should be able to access the previously created vhosts.



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
        */5 * * * * php /var/path/to/project/yii mails/process 50
    ```
    > Check `console\controllers\MailsController::actionProcess()` for all available command arguments.

## Updating app assets

To be able to autogenerate api doc, change and minify app assets, first you have to install `npm` and then run `npm install`.

After that you should be able to start [Grunt](http://gruntjs.com/getting-started) with the `grunt` command and just wait all tasks to complete.

> Check Gruntfile.js for all available tasks and options.
