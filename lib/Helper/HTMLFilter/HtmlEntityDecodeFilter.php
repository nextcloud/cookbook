<?php

namespace OCA\Cookbook\Helper\HTMLFilter;

class HtmlEntityDecodeFilter extends AbstractHtmlFilter {
	#[\Override]
	public function apply(string &$html): void {
		$html = html_entity_decode($html, ENT_NOQUOTES);
	}
}
