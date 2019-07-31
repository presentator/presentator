Presentator v2 SPA
======================================================================

![Interface screenshot](https://u.cubeupload.com/presentator/readme.png)

Presentator SPA is a frontend interface for the Presentator v2 REST API.
It is built with [Vue.js](https://vuejs.org/) and [Webpack](https://webpack.js.org/).

- [Installing](#installing)

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Installing

1. Download or clone the project repo.

2. Create a new `.env.local` file in the project root directory. Copy and edit the configuration settings from `.env` to suit your environment setup.

3. Run the appropriate console commands:

```bash
# installs dependencies
npm install

# starts a dev server with hot reload at localhost:8080
npm run serve

# generates i18n-report.json file with info about the current i18n app state
npm run i18n:report

# generates production ready bundle in dist/ directory
npm run build
```

> Built files are meant to be served over an HTTP server. Opening index.html over file:// won't work.
