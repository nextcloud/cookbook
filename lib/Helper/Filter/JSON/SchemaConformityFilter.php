<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Make sure the schema.org standard annotations are present in the JSON object.
 */
class SchemaConformityFilter extends AbstractJSONFilter {
	#[\Override]
	public function apply(array &$json): bool {
		$changed = false;
		if ($this->setJSONValue($json, '@context', 'http://schema.org')) {
			$changed = true;
		}
		if ($this->setJSONValue($json, '@type', 'Recipe')) {
			$changed = true;
		}
		return $changed;
	}
}
