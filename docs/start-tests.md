Presentator - Running tests
======================================================================

Presentator uses [Codeception](http://codeception.com/) as its primary test framework.
There are tests for each application component - `app/tests`, `api/tests` and `common/tests`.

Tests require an additional database, which will be cleaned up between tests.
Create a new database according to config in `common/config/test-local.php` and execute:

```
php yii_test migrate
```

Build the test suite:
```
/path/to/project/vendor/bin/codecept build
```

Then all applications tests can be started by running:
```
/path/to/project/vendor/bin/codecept run
```

Keep your tests always up to date. If a class, or functionality is deleted, corresponding tests should be deleted as well.

#### App tests
Contains Unit and Functional tests for all `app` application models and actions.
To run only `app` tests, navigate to the `app` dir and execute:
```
# run unit and functional tests
/path/to/project/vendor/bin/codecept run

# run only unit/integration tests
/path/to/project/vendor/bin/codecept run unit

# run only functional tests
/path/to/project/vendor/bin/codecept run functional
```

#### Api tests
Contains Unit and REST tests for all `api` application models and actions.
To run only `api` tests, navigate to the `api` dir and execute:
```
# run unit and functional tests
/path/to/project/vendor/bin/codecept run

# run only unit/integration tests
/path/to/project/vendor/bin/codecept run unit

# run only functional tests
/path/to/project/vendor/bin/codecept run functional
```

#### Common tests
Mainly Unit tests for all common AR models and classes.
Common fixtures are also located there.
To run only `common` tests, navigate to the `common` dir and execute:
```
/path/to/project/vendor/bin/codecept run
```

## Useful links
[Codeception Unit testing guide](http://codeception.com/docs/05-UnitTests)
[Codeception Function testing guide](http://codeception.com/docs/04-FunctionalTests)
[Codeception REST testing guide](http://codeception.com/docs/10-WebServices#REST)
[Yii Codeception module](http://codeception.com/for/yii)
[Speed up your tests using MySQL/MariaDB server with tmpfs datadir](https://github.com/martingeorg/tmpfs-mysql)
