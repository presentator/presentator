const path                          = require('path');
const VueLoaderPlugin               = require('vue-loader/lib/plugin');
const HtmlWebpackPlugin             = require('html-webpack-plugin');
const HtmlWebpackInlineSourcePlugin = require('html-webpack-inline-source-plugin');

module.exports = (env, argv) => ({
    // This is necessary because Figma's 'eval' works differently than normal eval
    devtool: argv.mode === 'production' ? false : 'inline-source-map',
    entry: {
        ui: './src/ui.js',
        main: './src/code.js',
    },
    resolveLoader: {
        modules: [path.join(__dirname, 'node_modules')],
    },
    module: {
        rules: [
            { test: /\.vue$/, loader: 'vue-loader', exclude: /node_modules/ },
            { test: /\.css$/, loader: [{ loader: 'style-loader' }, { loader: 'css-loader' }] },
            { test: /\.(png|jpg|gif|webp|svg)$/, loader: [{ loader: 'url-loader' }] },
        ],
    },
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            '@':    (__dirname + '/src'),
        }
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'build'),
    },
    plugins: [
        new HtmlWebpackPlugin({
            template: './src/ui.html',
            filename: 'ui.html',
            inlineSource: '.(js)$',
            chunks: ['ui'],
        }),
        new HtmlWebpackInlineSourcePlugin(),
        new VueLoaderPlugin(),
    ],
    node: {
        // prevent webpack from injecting useless setImmediate polyfill because Vue
        // source contains it (although only uses it if it's native).
        setImmediate: false,
        // prevent webpack from injecting mocks to Node native modules
        // that does not make sense for the client
        dgram: 'empty',
        fs: 'empty',
        net: 'empty',
        tls: 'empty',
        child_process: 'empty',
    }
});

