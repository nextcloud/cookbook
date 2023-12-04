<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\IL10N;

/**
 * Fix the image URL if no scheme was provided.
 *
 * Ensure the image URL has a scheme (http:// or https://) attached.
 */
class FixImageSchemeFilter extends AbstractJSONFilter {
	/** @var IL10N */
	private $l;

	public function __construct(IL10N $l) {
		$this->l = $l;
	}

	public function apply(array &$json): bool {
		if (substr($json['image'], 0, 2) === '//') {
			if (!isset($json['url'])) {
				throw new InvalidRecipeException($this->l->t('Could not guess image URL as no recipe URL was found.'));
			}
			if (preg_match('/^([a-zA-Z]+:)\/\//', $json['url'], $matches) !== 1) {
				throw new InvalidRecipeException($this->l->t('Could not guess image URL scheme from recipe URL %s', [$json['url']]));
			}

			$json['image'] = $matches[1] . $json['image'];
			return true;
		} else {
			return false;
		}
	}
}
