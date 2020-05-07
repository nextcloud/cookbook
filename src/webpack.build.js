const merge = require('webpack-merge')
const common = require('./webpack.config.js')
const FileManager = require('filemanager-webpack-plugin')

module.exports = merge(common, {
    mode: 'production',
    devtool: 'source-map',
    plugins: [
        new FileManager({
            onEnd: {
                copy: [
                    { source: 'dist/script.js', destination: '../js/vue.js' },
                    { source: 'dist/script.js.map', destination: '../js/vue.js.map' }
                ],
            },
        }),
    ],
})
