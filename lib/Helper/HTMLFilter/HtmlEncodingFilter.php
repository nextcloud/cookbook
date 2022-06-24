<?php

namespace OCA\Cookbook\Helper\HTMLFilter;

class HtmlEncodingFilter extends AbstractHtmlFilter {
	public function apply(string &$html): void {
		if (preg_match('/^<?xml encoding=/', $html) === 0) {
			$html = '<?xml encoding="UTF-8">' . $html;
		}
	}
}
