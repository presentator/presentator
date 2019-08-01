const webpack      = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: './src/Client.js',
    output: {
        libraryTarget: 'umd',
        library:       'PresentatorClient',
        filename:      'client.min.js',
        path:          (__dirname + '/dist'),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                include: [__dirname + '/src'],
                exclude: /node_modules/,
                loader: 'babel-loader',
                options: {
                    presets: [['@babel/preset-env', { 'modules': 'commonjs' }]],
                    plugins: ['add-module-exports'],
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
