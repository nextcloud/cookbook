const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.extends.push('stylelint-config-prettier', 'stylelint-config-idiomatic-order')

stylelintConfig.rules.indentation = null
stylelintConfig.rules['string-quotes'] = 'double'
stylelintConfig.rules['selector-list-comma-newline-after'] = 'always-multi-line'
stylelintConfig.rules['function-no-unknown'] = [true, {
	'ignoreFunctions': ["math.div"]
}]

module.exports = stylelintConfig
