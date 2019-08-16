const webpack = require('webpack');

module.exports = {
    lintOnSave: false,
    assetsDir: 'spa-resources',
    productionSourceMap: false,
    configureWebpack: {
        devServer: {
            watchOptions: {
                poll: true,
            },
        },
    },
    pluginOptions: {
        i18n: {
            locale:         'en-US',
            fallbackLocale: 'en-US',
            localeDir:      'messages',
            enableInSFC:    false,
        },
    },
}
