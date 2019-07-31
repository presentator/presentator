### Regular/Super User

By default all registered users has Regular User access rights (_access to only their own account and projects_).
For easier system administration you can set Super User access rights (_access to all system accounts and projects_) to one or more registered users (**available since v1.7+**).
Change the user's `type` column manually in your DB (_0 - Regular, 1 - Super_) or run one of the following console commands:

```bash
# sets Super User access
php yii users/super test@example.com

# sets Regular User access
php yii users/regular test@example.com
```


### Cron Jobs

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
