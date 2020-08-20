Presentator Starter
======================================================================

Presentator Starter is a skeleton Presentator installation setup best suited for production environment.

It wraps all required components for a Presentator installation in a single package and allows seamless upgrades just by using [Composer](https://getcomposer.org/).

- [Requirements](#requirements)
- [Installation](#installation)
- [Updates](#updates)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**

> If you prefer a dockerized version of the starter package, please check [presentator-docker](https://github.com/presentator/presentator-docker).


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

    In addition, here are some recommended `php.ini` configuration settings:
    ```
    post_max_size       = 64M
    upload_max_filesize = 64M
    max_execution_time  = 60
    memory_limit        = 256M
    ```

- [Composer](https://getcomposer.org/)

## Installation


1. Install through Composer:

    ```bash
    composer create-project presentator/starter /path/to/starter/
    ```

    > **For security reasons, if you are using a shared hosting service it is recommended to place the project files outside from your default public_html(www) directory!**

2. Setup a vhost/server address (eg. https://your-presentator.com/) and point it to `/path/to/starter/web/`.

    > By default a generic `.htaccess` file will be created for you after initialization. If you are using nginx, you could check the following [sample configuration](https://github.com/presentator/presentator/issues/120#issuecomment-539844456).

3. Create a new database (with `utf8mb4_unicode_ci` collation).

4. Adjust the **db**, **mailer** and other components configuration in `config/base-local.php` accordingly.

    > Check [base.php](https://github.com/presentator/presentator-api/blob/master/config/base.php) for all available options.

5. Adjust your environment specific parameters (public urls, support email, etc.) in `config/params-local.php` accordingly.

    > Check [params.php](https://github.com/presentator/presentator-api/blob/master/config/params.php) for all available options.

6. (optional) If needed, you could also adjust the frontend (aka. SPA) settings by editing the `extra.starter.spaConfig` key in your `composer.json` file.

    > Check [.env](https://github.com/presentator/presentator-spa/blob/master/.env) for all available options.

7. Run again `composer install` to make sure that the application is inited correctly.

6. (optional) Setup a cron task to process unread screen comments:

    ```bash
    # Every 30 minutes processes all unread screen comments and sends an email to the related users.
    */30 * * * * php /path/to/starter/yii mails/process-comments
    ```

**That’s it!** Check the application in your browser to verify that everything is working fine.

#### Additional console commands you may find useful

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

> For a finer control, check the packages version constraint in the `require` section of `/path/to/starter/composer.json`.
