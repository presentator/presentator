Presentator docker-compose production example set-up
======================================================================

Here you can find an example `docker-compose.yml` configuration that makes use of [Presentator Docker image](https://github.com/presentator/presentator-docker).

The set-up contains the following containers:

- **`jwilder/nginx-proxy`** - reverse proxy for easier vhosts management
- **`mariadb`** - MariaDB v10 database
- **`presentator`** - the application itself


## Quick start

**NB!** For simplicity the code below is using [docker-compose](https://docs.docker.com/compose/), but if you prefer you could use [docker stack deploy](https://docs.docker.com/engine/reference/commandline/stack_deploy/) + [docker swarm](https://docs.docker.com/engine/swarm/).

1. SSH login to you machine and clone the example directory files.

2. Update `mariadb` and `presentator` container environment variables in the `docker-compose.yml` file.

3. Update the following app configuration files:

    - `./php.ini`
    - `./vhosts.conf`
    - `./spa.json`
    - `./base-local.php`
    - `./params-local.php`

4. Open a terminal and run `docker-compose up -d`

**That's it!** You should be able to access the defined vhost in your browser.
