Creating a development environment with Docker is probably one of the easiest way to get started with the project. 

> Make sure that you have installed **[Docker](https://docs.docker.com/install/)** and **[Docker Compose](https://docs.docker.com/compose/install/)**.

1. Add the following line in your `/etc/hosts` file:

    ```
    127.0.0.1 adminer.local app.presentator.local api.presentator.local
    ```

2. Clone or download the [Presentator repository](https://github.com/ganigeorgiev/presentator) and execute the following commands:

    ```bash
    # start the docker daemon service (if is not started already)
    sudo systemctl start docker
    
    # navigate to the project root dir
    cd /path/to/presentator
    
    # start the project containers (nginx-proxy, mariadb, adminer and the application itself)
    docker-compose up
    
    # --> open new terminal window in the same working directory...
    
    # install app dependencies, db migrations, etc.
    ./docker/scripts/setup
    ```

**That's it!** You should be able to access the previously added vhosts in your browser:

- [adminer.local](http://adminer.local) - light and secure database management GUI tool
- [app.presentator.local](http://app.presentator.local) - the application web interface
- [api.presentator.local](http://api.presentator.local) - the application API

For easier development, the following helper docker scripts are available:

- **`./docker/scripts/setup`** - take care for the first-time application setup
- **`./docker/scripts/run`** - execute any commands within the app container, eg. `./docker/scripts/run php yii migrate`
- **`./docker/scripts/grunt`** - run grunt tasks, eg. `./docker/scripts/grunt css`
- **`./docker/scripts/codecept`** - run project tests, eg. `./docker/scripts/codecept unit`

> Check the project `/path/to/presentator/docker` directory for error logs, `php.ini` directives, mariadb config variables, etc.
