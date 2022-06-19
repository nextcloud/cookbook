<?php

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
