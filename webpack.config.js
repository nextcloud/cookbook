/**
 * Nextcloud Cookbook app
 * Main Webpack configuration file.
 * Different configurations for development and build runs
 *  are located in the appropriate files.
 */
const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')

module.exports = {

    entry:{
        vue: path.join(__dirname, 'src', 'main.js'),
    },
    output: {
        path: path.resolve(__dirname, './js'),
        publicPath: '/js/',
        filename: '[name].js',
        chunkFilename: '[name].js?v=[contenthash]',
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['vue-style-loader', 'css-loader'],
            },
            {
                test: /\.html$/,
                loader: 'vue-template-loader',
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.(png|jpg|gif)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]?[hash]'
                },
            },
            {
                test: /\.(eot|woff|woff2|ttf|svg)$/,
                loaders: 'file-loader',
                options: {
                    name: '[path][name].[ext]?[hash]'
                },
            },
        ],
    },
    plugins: [new VueLoaderPlugin()],
    resolve: {
        extensions: ['*', '.js', '.vue', '.json'],
        modules: [
            path.resolve(__dirname, './node_modules')
        ],
        symlinks: false,
    },

}
