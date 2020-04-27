const merge = require('webpack-merge')
const common = require('./webpack.config.js')
const FileManager = require('filemanager-webpack-plugin')

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-cheap-source-map',
    plugins: [
        new FileManager({
            onEnd: {
                copy: [
                    { source: 'dist/script.js', destination: '../js/vue.js' }
                ],
            },
        }),
    ],
})
