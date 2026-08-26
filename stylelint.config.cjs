// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.extends.push('stylelint-config-idiomatic-order')

stylelintConfig.rules['function-no-unknown'] = [true, {
	'ignoreFunctions': ["math.div"]
}]

module.exports = stylelintConfig
