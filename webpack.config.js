/**
 * Nextcloud Cookbook app
 * Main Webpack configuration file.
 * Different configurations for development and build runs
 *  are located in the appropriate files.
 */
const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
var LodashModuleReplacementPlugin = require('lodash-webpack-plugin')

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
                test: /\.(eot|woff|woff2|ttf)$/,
                loaders: 'file-loader',
                options: {
                    name: '[path][name].[ext]?[hash]'
                },
            },
            {
                test: /\.svg$/,
                loader: 'svg-inline-loader'
            },
            // this will apply to both plain `.scss` files
            // AND `<style lang="scss">` blocks in `.vue` files
            {
                test: /\.scss$/,
                use: [
                'vue-style-loader',
                'css-loader',
                'sass-loader'
                ]
            }
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new LodashModuleReplacementPlugin
    ],
    resolve: {
        extensions: ['*', '.js', '.vue', '.json'],
        modules: [
            'node_modules'
        ],
        symlinks: false,
    },

}
