<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\HTMLFilter;

abstract class AbstractHtmlFilter {
	/**
	 * Filter the HTML according to the rules of this class
	 *
	 * This class operates on the original HTML code as passed by reference and may therefore modify the HTML string.
	 *
	 * @param string $html The HTML code to be filtered
	 */
	abstract public function apply(string &$html): void;
}
