<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\HTMLFilter;

class HtmlEncodingFilter extends AbstractHtmlFilter {
	#[\Override]
	public function apply(string &$html): void {
		if (preg_match('/^<\?xml/', $html) === 0) {
			$html = '<?xml encoding="UTF-8">' . $html;
		}
	}
}
