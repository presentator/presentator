const webpack      = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: './src/index.js',
    output: {
        libraryTarget: 'umd',
        library:       'Client',
        filename:      'client.min.js',
        path:          (__dirname + '/dist'),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                include: [__dirname + '/src'],
                loader: 'babel-loader',
                options: {
                    presets: [['@babel/env', { 'modules': false }]],
                },
            },
        ],
    },
    resolve: {
        alias: {
            '@': (__dirname + '/src'),
        },
    },
    plugins: [
        new webpack.BannerPlugin(`${(new Date()).getFullYear()} Presentator API JS Client https://presentator.io`),
    ],
    optimization: {
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    keep_classnames: true,
                    keep_fnames:     true,
                },
            }),
        ],
    },
};
