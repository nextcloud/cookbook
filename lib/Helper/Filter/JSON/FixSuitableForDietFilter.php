<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the suitableForDiet field of a recipe.
 *
 * suitableForDiet is a schema.org RestrictedDiet property on the Recipe type.
 * It is stored as a comma-separated list of diet identifiers (e.g. "VeganDiet,VegetarianDiet").
 *
 * If the field is absent, it is initialised to an empty string.
 * If it is an array (as produced by some scrapers), it is merged into a comma-separated string.
 * Individual entries are trimmed and deduplicated; empty entries are discarded.
 *
 * @see https://schema.org/suitableForDiet
 * @see https://schema.org/RestrictedDiet
 */
class FixSuitableForDietFilter extends AbstractJSONFilter {
	private const SUITABLE_FOR_DIET = 'suitableForDiet';

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

	#[\Override]
	public function apply(array &$json): bool {
		if (!isset($json[self::SUITABLE_FOR_DIET])) {
			$json[self::SUITABLE_FOR_DIET] = '';
			return true;
		}

		if (!is_string($json[self::SUITABLE_FOR_DIET]) && !is_array($json[self::SUITABLE_FOR_DIET])) {
			$this->logger->info($this->l->t('Could not parse suitableForDiet for recipe {recipe}.', ['recipe' => $json['name']]));
			$json[self::SUITABLE_FOR_DIET] = '';
			return true;
		}

		if (is_string($json[self::SUITABLE_FOR_DIET])) {
			$diets = explode(',', $json[self::SUITABLE_FOR_DIET]);
		} else {
			$diets = $json[self::SUITABLE_FOR_DIET];
		}

		$textCleaner = $this->textCleaner;

		$diets = array_map(function ($diet) use ($textCleaner) {
			$diet = strip_tags($diet);
			$diet = trim($diet);
			$diet = preg_replace('/\s+/', ' ', $diet);
			$diet = $textCleaner->cleanUp($diet);
			return $diet;
		}, $diets);

		$diets = array_filter($diets, fn ($d) => ($d !== ''));
		$diets = array_unique($diets);

		$csd = implode(',', $diets);
		$changed = ($csd !== $json[self::SUITABLE_FOR_DIET]);
		$json[self::SUITABLE_FOR_DIET] = $csd;

		return $changed;
	}
}
