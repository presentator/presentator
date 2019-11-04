const { VueLoaderPlugin } = require('vue-loader');

module.exports = {
    entry: './src/main.js',
    output: {
        path: __dirname,
        filename: 'main.js',
        libraryTarget: 'commonjs2',
    },
    devtool: 'none', // prevent webpack from using eval() on my module
    externals: {
        uxp: 'uxp',
        scenegraph: 'scenegraph',
        application: 'application',
    },
    resolve: {
      alias: {
        'vue$': 'vue/dist/vue.esm.js',
        '@':    (__dirname + '/src'),
      },
      extensions: ['*', '.js', '.vue', '.json'],
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
    ],
};
