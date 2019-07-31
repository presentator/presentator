Each environment is represented by a set of files under the `environments` directory.
The `init` command is used to initialize an environment. What it really does is copy everything from the environment directory over to the root directory where all applications are.

By default there are two environments: `dev` and `prod`. First is for development. It has all the developer tools
and debug turned on. Second is for server deployments. It has debug and developer tools turned off.

Typically environment contains application bootstrap files such as `index.php` and config files suffixed with `-local.php`.
These are either personal configs of team members which are usually in `dev` environment or configs of specific servers.
For example, production database connection could be in `prod` environment `-local.php` config. These local configs are added to `.gitignore` and never pushed to source code repository.

In order to avoid duplication, configurations are overriding each other. For example, the `api` reads configuration in the following order:

- `common/config/main.php`
- `common/config/main-local.php`
- `api/config/main.php`
- `api/config/main-local.php`

Parameters are read in the following order:

- `common/config/params.php`
- `common/config/params-local.php`
- `api/config/params.php`
- `api/config/params-local.php`

Or in other words - **the later config file overrides the former.**
