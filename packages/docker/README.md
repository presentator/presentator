Presentator Docker image for production
======================================================================

This repo provides a `Dockerfile` to quickly build and set-up a production ready Docker image for [Presentator](https://github.com/presentator/presentator).

- [Versions](#versions)
- [Quick start](#quick-start)
- [Upgrade from v1](#upgrade-from-v1)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Versions

- `ganigeorgiev/presentator:latest`, `ganigeorgiev/presentator:2`
- `ganigeorgiev/presentator:2.11`, `ganigeorgiev/presentator:2.11.0`
- `ganigeorgiev/presentator:2.10`, `ganigeorgiev/presentator:2.10.1`, `ganigeorgiev/presentator:2.10.0`
- `ganigeorgiev/presentator:2.9`, `ganigeorgiev/presentator:2.9.3`, `ganigeorgiev/presentator:2.9.2`, `ganigeorgiev/presentator:2.9.1`, `ganigeorgiev/presentator:2.9.0`
- `ganigeorgiev/presentator:2.8`, `ganigeorgiev/presentator:2.8.3`, `ganigeorgiev/presentator:2.8.2`, `ganigeorgiev/presentator:2.8.1`, `ganigeorgiev/presentator:2.8.0`
- `ganigeorgiev/presentator:2.7`, `ganigeorgiev/presentator:2.7.2`, `ganigeorgiev/presentator:2.7.1`, `ganigeorgiev/presentator:2.7.0`
- `ganigeorgiev/presentator:2.6`, `ganigeorgiev/presentator:2.6.3`, `ganigeorgiev/presentator:2.6.2`, `ganigeorgiev/presentator:2.6.1`, `ganigeorgiev/presentator:2.6.0`
- `ganigeorgiev/presentator:2.5`, `ganigeorgiev/presentator:2.5.3`, `ganigeorgiev/presentator:2.5.2`, `ganigeorgiev/presentator:2.5.1`, `ganigeorgiev/presentator:2.5.0`
- `ganigeorgiev/presentator:2.4`, `ganigeorgiev/presentator:2.4.0`
- `ganigeorgiev/presentator:2.3`, `ganigeorgiev/presentator:2.3.3`, `ganigeorgiev/presentator:2.3.2`, `ganigeorgiev/presentator:2.3.1`, `ganigeorgiev/presentator:2.3.0`
- `ganigeorgiev/presentator:2.2`, `ganigeorgiev/presentator:2.2.2`, `ganigeorgiev/presentator:2.2.1`, `ganigeorgiev/presentator:2.2.0`
- `ganigeorgiev/presentator:2.1`, `ganigeorgiev/presentator:2.1.2`, `ganigeorgiev/presentator:2.1.1`, `ganigeorgiev/presentator:2.1.0`
- `ganigeorgiev/presentator:2.0`, `ganigeorgiev/presentator:2.0.6`, `ganigeorgiev/presentator:2.0.5`, `ganigeorgiev/presentator:2.0.4`
- `ganigeorgiev/presentator:1.13`, `ganigeorgiev/presentator:1.13.2`, `ganigeorgiev/presentator:1.13.1`, `ganigeorgiev/presentator:1.13.0`
- `ganigeorgiev/presentator:1.12`, `ganigeorgiev/presentator:1.12.0`
- `ganigeorgiev/presentator:1.11`, `ganigeorgiev/presentator:1.11.3`, `ganigeorgiev/presentator:1.11.2`, `ganigeorgiev/presentator:1.11.1`, `ganigeorgiev/presentator:1.11.0`
- `ganigeorgiev/presentator:1.10`, `ganigeorgiev/presentator:1.10.0`
- `ganigeorgiev/presentator:1.9`, `ganigeorgiev/presentator:1.9.1`, `ganigeorgiev/presentator:1.9.0`

> The tag versions correspond to the actual [release versions of Presentator](https://github.com/presentator/presentator/releases).


## Quick start
> If you are looking for a development Docker set-up, plase check the [main Presentator repository](https://github.com/presentator/presentator).

You could find an example deployment set-up with `docker-compose.yml` file in the [/docker-compose-example](https://github.com/presentator/presentator-docker/tree/master/docker-compose-example) directory.

Configurations are managed by simple mounting volumes to your container.
For most users, the following mounting points will need to be defined:

- `/var/www/html/web/storage` - indicates where your app storage files will be saved
- `/var/www/html/config/spa.json` - SPA configurations ([list with all options](https://github.com/presentator/presentator-spa/blob/master/.env))
- `/var/www/html/config/base-local.php` - API base configurations - `db`, `mailer`, etc.
- `/var/www/html/config/params-local.php` - API parameters - secret keys, urls, tokens duration, etc. ([list with all parameters](https://github.com/presentator/presentator-api/blob/master/config/params.php))


## Upgrade from v1
Presentator v2 comes with a lot of new features and has some breaking changes (including files and directory structure).
If you have previously installed Presentator v1, make sure to check the [upgrade instructions](https://github.com/presentator/presentator/blob/master/UPGRADE.md).
