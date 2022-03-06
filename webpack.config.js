/**
 * Nextcloud Cookbook app
 * Main Webpack configuration file.
 * Different configurations for development and build runs
 *  are located in the appropriate files.
 */
const path = require('path')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

const webpack = require('webpack')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const { merge } = require('webpack-merge')
const { env } = require('process')

module.exports = (env) => { return merge(webpackConfig, {
    entry: {
        guest: path.resolve(path.join('src', 'guest.js')),
    },
    plugins: [
        new CleanWebpackPlugin(),
        new webpack.DefinePlugin({
            '__webpack_use_dev_server__': env.dev_server || false,
        }),
    ],
}) }

// module.exports = {

//     module: {
//         rules: [
//             {
//                 test: /\.vue$/,
//                 loader: 'vue-loader',
//             },
//             {
//                 test: /\.css$/,
//                 use: [
//                     process.env.NODE_ENV !== 'production' ?
//                     MiniCssExtractPlugin.loader :
//                     { loader: 'vue-style-loader' },
//                     {
//                         loader: 'css-loader',
//                         options: {
//                             esModule: false
//                         }
//                     },
//                     // [sass-loader](/loaders/sass-loader)
//                     { loader: 'sass-loader' }
//                 ]
//             },
//             {
//                 test: /\.html$/,
//                 loader: 'vue-template-loader',
//             },
//             {
//                 test: /\.js$/,
//                 loader: 'babel-loader',
//                 exclude: /node_modules/,
//             },
//             {
//                 test: /\.(png|jpg|gif)$/,
//                 loader: 'file-loader',
//                 options: {
//                     name: '[name].[ext]?[hash]'
//                 },
//             },
//             {
//                 test: /\.(eot|woff|woff2|ttf)$/,
//                 loader: 'file-loader',
//                 options: {
//                     name: '[path][name].[ext]?[hash]'
//                 },
//             },
//             {
//                 test: /\.svg$/,
//                 loader: 'svg-inline-loader'
//             },
//             // this will apply to both plain `.scss` files
//             // AND `<style lang="scss">` blocks in `.vue` files
//             {
//                 test: /\.scss$/,
//                 use: [
//                     process.env.NODE_ENV !== 'production' ?
//                     MiniCssExtractPlugin.loader :
//                     { loader: 'vue-style-loader' },
//                     {
//                         loader: 'css-loader',
//                         options: {
//                             esModule: false
//                         }
//                     },
//                     { loader: 'sass-loader' }
//                 ]
//             }
//         ],
//     },

// }
