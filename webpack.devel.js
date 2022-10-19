const { merge } = require('webpack-merge')
const base = require('./webpack.config.js')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin

module.exports = (env) => merge(base(env), {
    plugins: env.BUNDLE_ANALYZER ? [
        new BundleAnalyzerPlugin(
            {
                openAnalyzer: false,
            }
        )
    ] : [],
})
