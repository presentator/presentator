Presentator - Updates
======================================================================

**Before you get started, it's a good idea to backup your database and all Presentator files.**

> **WARNING**: The update process will affect all core files used to run Presentator. If you have made any modifications to those files, your changes will be lost.


#### For v1.6.0+

Navigate to your project root directory and run the `update` console command:

```bash
# navigate to project root dir
cd /path/to/presentator

# run the update console command
php yii update
```


#### Before v1.6.0

1. Download the latest build from https://presentator.io/downloads/latest.

2. Extract and replace the archive in your project root directory (don't worry, your `main-local` and `params-local` configurations will be not deleted).

3. Delete the app installer directory:

    ```bash
    rm -rf /path/to/presentator/app/web/install
    ```

4. Run `php yii migrate/up`.

For follow-up updates follow the instuctions [For v1.6.0+](#for-v160)
