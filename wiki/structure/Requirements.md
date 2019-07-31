- Apache HTTP server

    > If you prefer Ngnix you'll need to manually setup redirect rules pointing to `/app/web/index.php` and `api/web/index.php`)

- _PHP 5.6-7.3_ with the following extensions:

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
