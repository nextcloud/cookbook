<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCP\IL10N;
use OCP\ILogger;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;

/**
 * Fix the nutrition information of a recipe.
 *
 * The description must be present and an array in all cases.
 * If is is not present, this filter will insert the empty array instead.
 *
 * This filter also removes empty entries in the array.
 */
class FixNutritionFilter extends AbstractJSONFilter {
	private const NUTRITION = 'nutrition';

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	public function __construct(
		IL10N $l,
		ILogger $logger
	) {
		$this->l = $l;
		$this->logger = $logger;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::NUTRITION])) {
			$json[self::NUTRITION] = [];
			return true;
		}

		if (!is_array($json[self::NUTRITION])) {
			$this->logger->info($this->l->t('Could not parse the nutrition information successfully for recipe {name}.', ['name' => $json['name']]));
			$json[self::NUTRITION] = [];
			return true;
		}

		$nutrition = $json[self::NUTRITION];

		$json[self::NUTRITION] = array_filter($json[self::NUTRITION]);
		return $json[self::NUTRITION] !== $nutrition;
	}
}
