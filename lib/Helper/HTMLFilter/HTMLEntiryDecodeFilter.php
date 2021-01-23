<?php

namespace OCA\Cookbook\Helper\HTMLFilter;

class HTMLEntiryDecodeFilter extends AbstractHTMLFilter {
	public function apply(string $html): void {
		$html = html_entity_decode($html);
	}
}
