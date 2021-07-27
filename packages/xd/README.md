Presentator Adobe XD plugin
======================================================================

<p align="center"><img src="https://i.imgur.com/Ogn23L4.png" alt="Plugin screenshots"></p>

Adobe XD plugin to export artboard renditions to Presentator.

- [Installing](#installing)
- [Development](#development)

> **OAUTH2 is not supported. The users can authenticate only via email and password ([#178](https://github.com/presentator/presentator/issues/178)).**

> **This repository is READ-ONLY.**
> **Report issues and send pull requests in the [main Presentator repository](https://github.com/presentator/presentator/issues).**


## Installing

#### From the Adobe XD marketplace

Open Adobe XD and go to *Add-ons > Plugins*. Search for **Presentator Export** and then click *Install*.

#### Manually

Download [`/dist/presentator.xdx`](https://raw.githubusercontent.com/presentator/presentator-xd/master/dist/presentator.xdx) and double click to install.


## Development

> The plugin is built with [Vue.js](https://vuejs.org/) and [Webpack](https://webpack.js.org/).
> It is intended to be used with [Presentator v2.x](https://github.com/presentator/presentator).
> The auto screen replace functionality requires Presentator v2.3+.

1. Download or clone the plugin repo in the [Adobe XD Develop Folder](https://adobexdplatform.com/plugin-docs/reference/structure/location.html)

2. Run the appropriate console commands:

```bash
# installs dependencies
npm install

# generates a development build
npm run dev

# generates a development build while watching for file changes
npm run watch

# generates production ready build
npm run build
```
