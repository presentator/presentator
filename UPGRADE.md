Presentator v1 to v2 upgrade instructions
======================================================================

Presentator v2 comes with a lot of changes, including database and files structure, and therefore the upgrade process requires some preliminary steps.

1. Backup your Presentator v1 database and files.

2. Setup a new Presentator v2 installation by following the [production installation instructions](README.md#installation).

    ***NB!** Create a new empty database for the Presentator v2 setup. Presentator v1 database will be used later to import the data from it.*

3. Once the Presentator v2 installation is complete, start the following interactive command to import your Presentator v1 data:

    ```bash
    php /path/to/presentator_v2/yii v1-import
    ```

    You'll be asked to enter:

    - path to Presentator v1 uploads directory (usually `/path/to/presentator_v1/app/web/uploads`)
    - Presentator v1 database config (dsn, user, password)

Once the import process is complete, verify that your Presentator v2 setup still works and you can safely remove your Presentator v1 database and files.
