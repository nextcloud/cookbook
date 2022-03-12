const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.extends.push('stylelint-config-prettier', 'stylelint-config-idiomatic-order')

stylelintConfig.rules.indentation = null
stylelintConfig.rules['string-quotes'] = 'double'
stylelintConfig.rules['selector-list-comma-newline-after'] = 'always-multi-line'

module.exports = stylelintConfig
