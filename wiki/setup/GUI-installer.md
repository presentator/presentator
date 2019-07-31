Presentator comes with a small GUI installer.
This type of installation is suitable for production environment.

1. Create a new database (with `utf8_general_ci` or `utf8_unicode_ci` collation).

2. Download the latest build from https://presentator.io/downloads/latest and extract it to your server directory.

    > For security reasons, if you are using a shared hosting service it is recommended to extract the archvie files **outside** from your default **public_html(www)** directory!

3. Setup your domain (or create a new subdomain) and point it to the main application web directory, eg. `/path/to/presentator/app/web`.

    > Optional: if you are intending to make use of the API of the platform, create a subdomain and point it to the api application web directory - `/path/to/presentator/api/web`.

4. Navigate to `https://my-site.example/install` and follow the instructions of the graphic installer.

    > You may also want to check `https://my-site.example/requirements.php` to ensure that your server meets the minimum requirements.

![GUI Installer](images/installer.png)

After the installer completes you should be able to access and start using the platform at `https://my-site.example`.

> Don't worry, you can always change later the application configurations (db, mailer, etc.) by editting `/path/to/presentator/common/config/main-local.php` and `/path/to/presentator/common/config/params-local.php`
