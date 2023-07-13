const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.extends.push('stylelint-config-idiomatic-order')

stylelintConfig.rules.indentation = null
stylelintConfig.rules['string-quotes'] = 'double'
stylelintConfig.rules['function-no-unknown'] = [true, {
	'ignoreFunctions': ["math.div"]
}]

module.exports = stylelintConfig
