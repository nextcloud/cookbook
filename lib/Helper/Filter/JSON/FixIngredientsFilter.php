<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;

/**
 * Fix the ingredients list.
 *
 * The recipeIngredients entry is mandatory for the recipes and must be an array.
 * This filter ensures, an entry is present.
 *
 * If no entry is found, an empty array is added.
 * If there is already an array present, the entries are cleaned up to prevent malicious chars to be present.
 */
class FixIngredientsFilter extends AbstractJSONFilter {
	private const INGREDIENTS = 'recipeIngredient';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(IL10N $l, LoggerInterface $logger, TextCleanupHelper $textCleanupHelper) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::INGREDIENTS])) {
			$json[self::INGREDIENTS] = [];
			return true;
		}

		if (!is_array($json[self::INGREDIENTS])) {
			throw new InvalidRecipeException($this->l->t('Could not parse recipe ingredients. It is no array.'));
		}

		$ingredients = $json[self::INGREDIENTS];

		$ingredients = array_map(function ($t) {
			$t = trim($t);
			$t = $this->textCleaner->cleanUp($t, false);
			return $t;
		}, $ingredients);
		$ingredients = array_filter($ingredients, fn ($t) => ($t));
		$ingredients = array_values($ingredients);

		$changed = $ingredients !== $json[self::INGREDIENTS];
		$json[self::INGREDIENTS] = $ingredients;
		return $changed;
	}
}
