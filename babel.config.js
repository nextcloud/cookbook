const babelConfig = require('@nextcloud/babel-config');

// https://jestjs.io/docs/getting-started#using-typescript
babelConfig.presets.push('@babel/preset-typescript');

module.exports = babelConfig;
