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
    // You can add this to allow acces in the network. You will have to adopt the public path in main.js as well!
    // devServer: {
    //     host: "0.0.0.0",
    // },
    plugins: [
        new CleanWebpackPlugin(),
        new webpack.DefinePlugin({
            '__webpack_use_dev_server__': env.dev_server || false,
        }),
    ],
}) }

