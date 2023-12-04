<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the recipe yield field.
 *
 * This filter makes sure that the field exists.
 * It can be either null or an integer value.
 *
 * There is an heuristics in place to handle arrays which is not fool-proof.
 *
 * If a string is given, it is checked if it can be converted.
 * If not, a number within the string is searched for.
 * If multiple are found, the maximal one is assumed.
 * If even this is not found, a fallback value of 1 serving is assumed.
 */
class FixRecipeYieldFilter extends AbstractJSONFilter {
	private const YIELD = 'recipeYield';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(IL10N $l, LoggerInterface $logger) {
		$this->l = $l;
		$this->logger = $logger;
	}

	public function apply(array &$json): bool {
		if (!array_key_exists(self::YIELD, $json)) {
			$json[self::YIELD] = null;
			return true;
		}

		$changed = false;

		if (is_array($json[self::YIELD]) && count($json[self::YIELD]) === 1) {
			// It is just packed into an array, unpack it
			$json[self::YIELD] = reset($json[self::YIELD]);
			$changed = true;
		}

		if (is_int($json[self::YIELD])) {
			return $changed;
		}

		if (is_float($json[self::YIELD]) || is_double($json[self::YIELD])) {
			$json[self::YIELD] = (int) $json[self::YIELD];
			return true;
		}

		if ($json[self::YIELD] === null) {
			return $changed;
		}

		if (is_array($json[self::YIELD])) {
			assert(count($json[self::YIELD]) !== 1);

			// Heuristics: Array with multiple entries.
			// XXX How to parse an array correctly?
			$this->logger->debug($this->l->t('Using heuristics to parse the "recipeYield" field representing the number of servings of recipe {name}.', ['name' => $json['name']]));
			$json[self::YIELD] = join(' ', $json[self::YIELD]);
		}

		// Search the string for digits
		$count = preg_match_all('/\d+/', $json[self::YIELD], $matches);
		if ($count > 0) {
			// Only look at complete integer strings (full regex matches)
			$matches = $matches[0];
			sort($matches);
			$last = end($matches);

			$this->logger->debug($this->l->n(
				'Only a single number was found in the "recipeYield" field. Using it as number of servings.',
				'There are %n numbers found in the "recipeYield" field. Using the highest number found as number of servings.',
				$count
			));

			$json[self::YIELD] = (int) $last;
			return true;
		}

		// We did not find anything useful.
		$this->logger->info($this->l->t('Could not parse "recipeYield" field. Falling back to 1 serving.'));
		$json[self::YIELD] = 1;
		return true;
	}
}
