<?php

namespace OCA\Cookbook\Helper\HTMLFilter;

class HtmlEntiryDecodeFilter extends AbstractHtmlFilter {
	public function apply(string $html): void {
		$html = html_entity_decode($html);
	}
}
