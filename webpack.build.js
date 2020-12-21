const merge = require('webpack-merge')
const common = require('./webpack.config.js')
const TerserPlugIn = require('terser-webpack-plugin')
const CompressionPlugin = require('compression-webpack-plugin')

module.exports = merge(common, {
    mode: 'production',
    devtool: 'source-map',
    optimization: {
            minimizer: [
                new TerserPlugIn({
                    terserOptions:{	
                        //sideEffects: false,
                        // All files have side effects, and none can be tree-shaken
                        // {
                        //   "./src/file2.js"
                        //  ]
                        // }
                        output:{
                            comments: false
                        }
                    },
                    sourceMap: true
                })
            ],
        },
        plugins: [
            new CompressionPlugin({
                test: /\.js(\?.*)?$/i,
            }),
        ],
        performance: {
            hints: "warning",
            // Calculates sizes of gziped bundles.
            assetFilter: function (assetFilename) {
                return assetFilename.endsWith(".js.gz");
            },
        }
})
