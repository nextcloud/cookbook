const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.extends.push('stylelint-config-idiomatic-order')

stylelintConfig.rules['function-no-unknown'] = [true, {
	'ignoreFunctions': ["math.div"]
}]

module.exports = stylelintConfig
