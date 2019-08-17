Presentator Starter
======================================================================

Presentator Starter is a skeleton Presentator installation setup best suited for production environment.

It wraps all required components for a Presentator installation in a single package and allows seamless upgrades just by using [Composer](https://getcomposer.org/).

- [Requirements](#requirements)
- [Installation](#installation)
- [Updating](#updating)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Requirements

The only requirements are those from the [Presentator API](https://github.com/presentator/presentator-api/blob/master/README.md#requirements).


## Installation

> If you prefer a dockerized version of the starter package, please check [presentator-docker](https://github.com/presentator/presentator-docker).

Before getting started make sure that you have checked the project requirements and installed [Composer](https://getcomposer.org/).

1. Either [download the latest repo archive](https://github.com/presentator/presentator-starter/archive/master.zip), or install through Composer:

    ```bash
    composer create-project presentator/starter /path/to/starter/
    ```

    > **For security reasons, if you are using a shared hosting service it is recommended to place the project files outside from your default public_html(www) directory!**

2. Setup a vhost/server address (eg. https://my-presentator.com/) and point it to `/path/to/starter/web/`.

3. Create a new database (with `utf8mb4_unicode_ci` or `utf8_unicode_ci` collation) and edit the necessary Presentator API environment config files located in the `/path/to/starter/config/`.

    > Usually only `base-local.php` and `params-local.php` config files need to change.

    > Check the corresponding [non `-local` config files](https://github.com/presentator/presentator-api/blob/master/config) for all available options.

4. Overwrite the default Presentator SPA configurations by editing the `extra.starter.spaConfig` key of `/path/to/starter/composer.json`.

    > All available Presentator SPA configurations could be found in the [base SPA `.env` file](https://github.com/presentator/presentator-spa/blob/master/.env).


5. Run `composer install` while in the project root directory.

6. (optional) Setup a cron task to process unread screen comments:

    ```bash
    # Every 30 minutes processes all unread screen comments and sends an email to the related users.
    */30 * * * * php /path/to/starter/yii mails/process-comments
    ```

**That’s it!** Check the application in your browser to verify that everything is working fine.

Additional console commands you may found useful:

```bash
# set Super User access rights to a single User model
php /path/to/starter/yii users/super test@example.com

# set Regular User access rights to a single User model
php /path/to/starter/yii users/regular test@example.com

# regenerates all screen thumbs
php /path/to/starter/yii screens/generate-thumbs
```


## Updating

To update your Presentator application to the latest available version, just run `composer update` while in the project root directory.

> For a finer control, check the packages version constraint in the `require` section of `/path/to/starter/composer.json`.
