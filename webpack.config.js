/**
 * Nextcloud Cookbook app
 * Main Webpack configuration file.
 * Different configurations for development and build runs
 *  are located in the appropriate files.
 */
const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const LodashModuleReplacementPlugin = require('lodash-webpack-plugin')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")

module.exports = {

    entry: {
        vue: path.join(__dirname, 'src', 'main.js'),
        guest: path.join(__dirname, 'src', 'guest.js'),
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
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.css$/,
                use: [
                    process.env.NODE_ENV !== 'production' ?
                    MiniCssExtractPlugin.loader :
                    { loader: 'vue-style-loader' },
                    {
                        loader: 'css-loader',
                        options: {
                            esModule: false
                        }
                    },
                    // [sass-loader](/loaders/sass-loader)
                    { loader: 'sass-loader' }
                ]
            },
            {
                test: /\.html$/,
                loader: 'vue-template-loader',
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
                loader: 'file-loader',
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
                    process.env.NODE_ENV !== 'production' ?
                    MiniCssExtractPlugin.loader :
                    { loader: 'vue-style-loader' },
                    {
                        loader: 'css-loader',
                        options: {
                            esModule: false
                        }
                    },
                    { loader: 'sass-loader' }
                ]
            }
        ],
    },
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: "[name].css",
            // chunkFilename: "[id].css",
          }),
        new VueLoaderPlugin(),
        new LodashModuleReplacementPlugin,
    ],
    resolve: {
        extensions: ['*', '.js', '.vue', '.json'],
        modules: [
            'node_modules'
        ],
        symlinks: false,
    },

}
