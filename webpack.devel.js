const merge = require('webpack-merge')
const common = require('./webpack.config.js')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-cheap-source-map',
    plugins: [
        new BundleAnalyzerPlugin()
    ],
})
