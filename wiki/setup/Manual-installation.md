Rather than using the [GUI](GUI-installer), you could always install the application manually.
This type of installation is suitable for both production and development setup.

Before getting started make sure that you have checked the project [requirements](Requirements) and installed [Composer](https://getcomposer.org/).

1. Clone or download the project repository
    
    ```bash
    git clone https://github.com/ganigeorgiev/presentator.git
    ```

2. Setup a vhost/server address for the **app**, eg. `http://app.presentator.local/` and point it to `/app/web`.

3. Setup a vhost/server address for the **api**, eg. `http://api.presentator.local/` and point it to `/api/web`.

4. Open a console terminal and execute:

    ```bash
    # navigate to the project root dir
    cd /path/to/presentator
    
    # install vendor dependencies
    composer install
    
    # execute the init command and select the appropriate environment (dev or prod)
    php init
    ```

5. Create a new database (with `utf8_general_ci` or `utf8_unicode_ci` collation) and adjust the `database` and `mailer` components in `common/config/main-local.php` accordingly.

6. Adjust the applications required parameters in `common/config/params-local.php`.

7. Open a console terminal and apply DB migrations.

    ```bash
    php /path/to/project/yii migrate
    ```

**That's it!** You should be able to access the previously defined server addresses in your browser.


### Updating app assets

To be able to autogenerate api doc, change and minify app assets, first you have to install `npm` and then run `npm install`.

After that you should be able to start [Grunt](http://gruntjs.com/getting-started) with the `grunt` command and just wait all tasks to complete.

> Check Gruntfile.js for all available tasks and options.
