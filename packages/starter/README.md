Presentator Starter
======================================================================

Presentator Starter is a skeleton Presentator installation setup best suited for production environment.

It wraps all required components for a Presentator installation in a single package and allows seamless upgrades just by using [Composer](https://getcomposer.org/).

- [Requirements](#requirements)
- [Installation](#installation)
- [Updates](#updates)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Requirements

The only requirements are those from the [Presentator API](https://github.com/presentator/presentator-api/blob/master/README.md#requirements).


## Installation

> If you prefer a dockerized version of the starter package, please check [presentator-docker](https://github.com/presentator/presentator-docker).

Before getting started make sure that you have checked the project requirements and installed [Composer](https://getcomposer.org/).

1. Install through Composer:

    ```bash
    composer create-project presentator/starter /path/to/starter/
    ```

    > **For security reasons, if you are using a shared hosting service it is recommended to place the project files outside from your default public_html(www) directory!**

2. Setup a vhost/server address (eg. https://your-presentator.com/) and point it to `/path/to/starter/web/`.

    > By default a generic `.htaccess` file will be created for you after initialization. If you are using nginx, you could check the following [sample configuration](https://github.com/presentator/presentator/issues/120#issuecomment-539844456).

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

#### Additional console commands you may found useful

```bash
# set Super User access rights to a single User model
php /path/to/starter/yii users/super test@example.com

# set Regular User access rights to a single User model
php /path/to/starter/yii users/regular test@example.com

# regenerates all screen thumbs
php /path/to/starter/yii screens/generate-thumbs
```

#### Allow 3rd party authentication (OAuth2)

The default `base-local.php` comes with commented [various auth clients configurations](https://github.com/presentator/presentator/blob/master/packages/api/environments/prod/config/base-local.php#L40-L79).

For example, if you want to allow your users to login with their Facebook account:

1. [Register a Facebook app](https://developers.facebook.com/docs/apps#register) (only the account email is required, so there is no need for any special permissions).

    > Make sure for **Valid OAuth Redirect URIs** to set the same url as `authClientRedirectUri` from your `params-local.php` (by default it should be something like https://your-presentator.com/#/auth-callback).

2. Register the Facebook auth client in your `base-local.php`:

    ```php
    'components' => [
        ...
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'facebook' => [
                    'class'        => 'yii\authclient\clients\Facebook',
                    'clientId'     => 'YOUR_APP_CLIENT_ID',
                    'clientSecret' => 'YOUR_APP_CLIENT_SECRET',
                ],
            ],
        ],
    ]
    ```

#### Different storage mechanism

By default all uploaded files are stored locally on your server in `/path/to/starter/web/storage`.
If you are worried about disk space or want to store your uploads on a different server, you could override the default `fs` component configuration.

For example, if you want to store your files on AWS S3:

1. Update the `baseStorageUrl` in your `params-local.php`

    ```php
    // base public url to the storage directory (could be also a cdn address if you use S3 or other storage mechanism)
    'baseStorageUrl' => 'https://example.com/storage',
    ```

2. Add the AWS S3 filesystem adapter to your dependencies

    ```bash
    composer require league/flysystem-aws-s3-v3
    ```

3. Override the default `fs` component in your `base-local.php`:

    ```php
    'components' => [
        'fs' => new \yii\helpers\ReplaceArrayValue([
            'class'  => 'creocoder\flysystem\AwsS3Filesystem',
            'key'    => 'YOUR_KEY',
            'secret' => 'YOUR_SECRET',
            'bucket' => 'YOUR_BUCKET',
            'region' => 'YOUR_REGION',
            // other parameters:
            // 'version'  => 'latest',
            // 'baseUrl'  => 'YOUR_BASE_URL',
            // 'prefix'   => 'YOUR_PREFIX',
            // 'options'  => [],
            // 'endpoint' => 'http://your-url'
        ]),
        ...
    ]
    ```

    > You may also want to check [#138](https://github.com/presentator/presentator/issues/138) and [#141](https://github.com/presentator/presentator/issues/141).

For other adapters and more options, go to https://github.com/creocoder/yii2-flysystem.


## Updates

To update your Presentator application to the latest available version, just run `composer update` while in the project root directory.

> For a finer control, check the packages cversion constraint in the `require` section of `/path/to/starter/composer.json`.
