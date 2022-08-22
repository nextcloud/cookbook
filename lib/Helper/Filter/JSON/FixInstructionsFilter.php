<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\IL10N;
use OCP\ILogger;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCA\Cookbook\Service\JsonService;

/**
 * Fix the instructions list.
 *
 * The instructions are required to be an array of strings.
 * The Schema.org standard allows for different encoding variants:
 * There can be arrays of Text, ListItem and CreativeWork objects.
 *
 * This filter will make sure, that the recipeInstructions entry is at least an empty list.
 * Further, it parses the original entries of the array if present and try to convert them into plain text.
 * Nested lists of instructions using ListItem -> ItemList -> ListItem chains are flattened.
 *
 * If the instructions are a plain string, the string is split according to newline chars and put into an array.
 *
 * After that, some cleanup is done to prevent malicious code insertions.
 *
 * @todo Parse HTML entries for bold/italic/underlined text and create markdown
 */
class FixInstructionsFilter extends AbstractJSONFilter {
	private const INSTRUCTIONS = 'recipeInstructions';

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	/** @var JsonService */
	private $jsonService;

	public function __construct(
		IL10N $l,
		ILogger $logger,
		TextCleanupHelper $textCleanupHelper,
		JsonService $jsonService
	) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
		$this->jsonService = $jsonService;
	}

	public function apply(array &$json): bool {
		if (! isset($json[self::INSTRUCTIONS])) {
			$json[self::INSTRUCTIONS] = [];
			return true;
		}

		if ($json[self::INSTRUCTIONS] === null || $json[self::INSTRUCTIONS] === '') {
			$json[self::INSTRUCTIONS] = [];
			return true;
		}

		$instructions = $json[self::INSTRUCTIONS];

		if (is_string($json[self::INSTRUCTIONS])) {
			$instructions = $this->parseStringUsingHtmlEntities($json);
			if ($instructions === null) {
				$instructions = $this->parseString($json);
			}
		}

		if (!is_array($instructions)) {
			throw new InvalidRecipeException($this->l->t('Could not parse recipe instructions as they are no array.'));
		}

		if ($this->jsonService->isSchemaObject($instructions, 'ItemList', false)) {
			// We have a single ItemList as instructions.
			$instructions = $this->flattenItemList($instructions);
		} else {
			// We got an array of whatever type
			foreach ($instructions as $key => $value) {
				if (is_string($value)) {
					$instructions[$key] = [$value];
					continue;
				}

				if ($this->jsonService->isSchemaObject($value, 'ItemList', false)) {
					$instructions[$key] = $this->flattenItemList($value);
					continue;
				}

				throw new InvalidRecipeException($this->l->t('Cannot parse recipe: Unknown object found during flattening of instructions.'));
			}

			ksort($instructions);
			$instructions = array_merge(...$instructions);
		}


		$instructions = array_map(function ($x) {
			$x = trim($x);
			$x = $this->textCleaner->cleanUp($x, false);

			return $x;
		}, $instructions);
		$instructions = array_filter($instructions);

		$changed = $instructions !== $json[self::INSTRUCTIONS];
		$json[self::INSTRUCTIONS] = $instructions;
		return $changed;
	}

	/**
	 * Parses a single ItemList object and extracts all items recursively.
	 *
	 * Note: This will not check the object if it is a valid ItemList but silently assume so.
	 * The user is responsible to guarantee this.
	 *
	 * @param array $list
	 * @return array<string> The list of instructions
	 */
	private function flattenItemList(array $list): array {
		$elements = $list['itemListElement'];

		$newElements = [];
		foreach ($elements as $key => $element) {
			if (is_string($element)) {
				$newElements[$key] = [$element];
				continue;
			}

			if ($this->jsonService->isSchemaObject($element, 'ListItem', false)) {
				$newKey = $element['position'] ?? $key;
				$newElements[$newKey] = $this->parseListItem($element['item']);
				continue;
			}

			throw new InvalidRecipeException($this->l->t('Cannot parse recipe: Unknown object found during flattening of instructions.'));
		}

		ksort($newElements);
		$elements = array_merge(...$newElements);

		return $elements;
	}

	/**
	 * Parses a single ListItem entry in a ItemList.
	 *
	 * @param array|string $item The item to parse
	 * @return array The containing instructions as a flat list
	 */
	private function parseListItem($item): array {
		if (is_string($item)) {
			return [$item];
		}

		if ($this->jsonService->isSchemaObject($item, 'ItemList', false)) {
			return $this->flattenItemList($item);
		}

		throw new InvalidRecipeException($this->l->t('Cannot parse recipe: Unknown object found during flattening of instructions.'));
	}

	private function parseStringUsingHtmlEntities(array $json): ?array {
		$count = preg_match_all('/<(p|li)>(.*?)<\/\1>/', $json[self::INSTRUCTIONS], $matches);

		if ($count === 0) {
			$this->logger->debug($this->l->t('Did not find any p or li entries in the raw string of the instructions.'));
			return null;
		}

		$instructions = array_map(fn ($x) => ($this->textCleaner->cleanUp($x)), $matches[2]);
		$instructions = array_filter($instructions);

		if (count($instructions) === 0) {
			return null;
		} else {
			return $instructions;
		}
	}

	private function parseString(array $json): array {
		$pieces = preg_split('/[\n\r]+/', $json[self::INSTRUCTIONS]);
		$pieces = array_map(fn ($x) => (strip_tags($x)), $pieces);
		$pieces = array_filter($pieces);
		return $pieces;
	}
}
