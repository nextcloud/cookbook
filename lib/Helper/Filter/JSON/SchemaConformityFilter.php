<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Make sure the schema.org standard annotations are present in the JSON object.
 */
class SchemaConformityFilter extends AbstractJSONFilter {
	public function apply(array &$json): bool {
		$changed = false;

		$changed |= $this->setJSONValue($json, '@context', 'http://schema.org');
		$changed |= $this->setJSONValue($json, '@type', 'Recipe');

		return (bool)$changed;
	}
}
