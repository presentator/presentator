<p align="center">
    <a href="https://presentator.io" target="_blank" rel="noopener"><img src="https://u.cubeupload.com/presentator/readmeheader.png" alt="Presentator hero image"></a>
</p>

<p align="center">
    <a href="https://www.yiiframework.com/" target="_blank" rel="noopener"><img src="https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat" alt="Yii2"></a>
    <a href="https://github.com/presentator/presentator/releases" target="_blank" rel="noopener"><img src="https://img.shields.io/github/release/presentator/presentator.svg" alt="Latest Release"></a>
    <a href="https://gitter.im/presentatorio/presentator" target="_blank" rel="noopener"><img src="https://badges.gitter.im/presentatorio/presentator.png" alt="Gitter chat"></a>
</p>

---

[Presentator](https://presentator.io) is design presentation and collaboration platform.

The project is organized in a monorepo and consists of several decoupled packages:

- [starter](packages/starter)
- [api](packages/api)
- [spa](packages/spa)
- [js-client](packages/js-client)


## Upgrade from v1

If you have previously installed Presentator v1, please follow the [upgrade instructions](UPGRADE.md).


## Installation

#### Production

For production environment it is recommended to follow the [starter package instructions](packages/starter).

#### Development

Creating a development environment with Docker is probably one of the easiest way to get started with the project.

> If you don't want to use the Docker dev setup, you can always follow each sub package instructions and install them manually.

1. Make sure that you have installed **[Docker](https://docs.docker.com/install/)** and **[Docker Compose](https://docs.docker.com/compose/install/)**.

2. Clone or download the Presentator repository and execute the following commands:

    ```bash
    # start the docker daemon service (if is not started already)
    sudo systemctl start docker

    # navigate to the project root dir
    cd /path/to/presentator

    # start all project containers in the background
    docker-compose up -d

    # initial api and spa setup (required only the first time you setup the application)
    ./compose/scripts/setup

    # start the application web interface
    ./compose/scripts/spa npm run serve
    ```

**That's it!** You should be able to access the following addresses in your browser:

- http://localhost:8080 - the application web interface
- http://localhost:8081 - the application API server
- http://localhost:8082 - Adminer (light and secure database management GUI tool)

The following helper docker scripts are available:

```bash
# take care for the first-time application setup
./compose/scripts/setup

# run api tests, eg. `./compose/scripts/codecept run`
./compose/scripts/codecept ...

# execute any command within the *api* container, eg. `./compose/scripts/api php yii migrate`
./compose/scripts/api ...

# execute any command within the *spa* container, eg. `./compose/scripts/spa npm run build`
./compose/scripts/spa ...

# execute any command within the *jsclient* container, eg. `./compose/scripts/jsclient npm install`
./compose/scripts/jsclient ...

```

To stop and shutdown every container just run `docker-compose down` while in the project root directory.


## Contributing
[Presentator](https://presentator.io) is Open Source project licensed under the [BSD-3 License](LICENSE.md).

You could join our team and help us by:

- [Translate messages](https://www.transifex.com/presentatorio/web-platflorm)
- [Report issues and suggest new features](https://github.com/presentator/presentator/issues)
- [Fix bugs and send pull requests](https://github.com/presentator/presentator/pulls)
- [Donate a small amount via PayPal or Patreon](https://presentator.io/support-us)
