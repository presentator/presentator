### DB

![img](images/erd.png)

The project makes use of the following DB conventions:

- use InnoDB table engine
- use `utf8_unicode_ci` collation ([difference between general_ci](http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci#answer-766996))
- add `Rel` suffix to the junction tables
- table names must be in camelCase format (eg. `userProjectRel`)
- table columns must be in camelCase format (eg. `passwordResetToken`)
- Whenever is possible add named foreign keys and indexes (eg. `fk_setting_to_user`)
- each change must be applied via Yii migration tool

> The camelCased naming is used to be consistent with:
> 1. ActiveRecord DB properties, class properties and relation methods
> 2. ActiveRecord and their related form model properties (eg. `User` and `UserForm`)
> 3. REST API response keys


### PHP

**Each php class must follow the accepted [PSR standards](http://www.php-fig.org/psr/#accepted).**

In addition:

- each public method should have a related Unit test (see [Running tests](start-tests.md)).
- each method should have comment block tags based on [phpDocumentor tags](https://phpdoc.org/docs/latest/internals/tags.html) (method description is optional)
- REST API actions must documented following [apiDoc params](http://apidocjs.com/#params)


### JS/HTML/SASS

> coming soon...


### Additional notes

- [Yii 2 Guide](http://www.yiiframework.com/doc-2.0/guide-index.html)
- [Yii 2 Class reference](http://www.yiiframework.com/doc-2.0/index.html)
- [Yii 2 Coding standards](https://github.com/yiisoft/yii2-coding-standards)
