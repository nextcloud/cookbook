<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\HTMLFilter;

class HtmlEntityDecodeFilter extends AbstractHtmlFilter {
	#[\Override]
	public function apply(string &$html): void {
		$html = html_entity_decode($html, ENT_NOQUOTES);
	}
}
