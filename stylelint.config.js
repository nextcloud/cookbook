const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.rules.indentation = 4
stylelintConfig.rules['string-quotes'] = 'double'
stylelintConfig.rules['selector-list-comma-newline-after'] = 'always-multi-line'

module.exports = stylelintConfig
