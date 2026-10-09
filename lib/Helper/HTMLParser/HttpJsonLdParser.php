<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;

/**
 * This class is an AbstractHtmlParser which tries to extract a JSON+LD script from the HTML page.
 * @author Christian Wolf
 */
class HttpJsonLdParser extends AbstractHtmlParser {
	/**
	 * @var JsonService
	 */
	private $jsonService;

	public function __construct(IL10N $l10n, JsonService $jsonService) {
		parent::__construct($l10n);

		$this->jsonService = $jsonService;
	}

	#[\Override]
	public function parse(\DOMDocument $document, ?string $url): array {
		$xpath = new \DOMXPath($document);

		$json_ld_elements = $xpath->query("//*[@type='application/ld+json']");

		foreach ($json_ld_elements as $json_ld_element) {
			if (!$json_ld_element || !$json_ld_element->nodeValue) {
				continue;
			}

			try {
				return $this->parseJsonLdElement($json_ld_element);
			} catch (HtmlParsingException $ex) {
				// Parsing failed for this element. Let's see if there are more...
			}
		}

		throw new HtmlParsingException($this->l->t('Could not find recipe in HTML code.'));
	}

	/**
	 * Parse a JSON+LD element in the DOM tree for a recipe
	 *
	 * @param \DOMNode $node The node to parse
	 * @throws HtmlParsingException The node does not contain a valid recipe
	 * @return array The recipe as an associate array
	 */
	private function parseJsonLdElement(\DOMNode $node): array {
		return $this->parseJsonLd($node->nodeValue);
	}

	/**
	 * Parse a JSON+LD string for a recipe
	 *
	 * @param string $string The JSON+LD content, optionally wrapped in a script tag
	 * @param bool $assumeSchemaContext Treat objects without @context as schema.org objects
	 * @throws HtmlParsingException The string does not contain a valid recipe
	 * @return array The recipe as an associate array
	 */
	public function parseJsonLd(string $string, bool $assumeSchemaContext = false): array {
		$string = preg_replace('/^\s*<script[^>]*>|<\/script>\s*$/i', '', $string) ?? $string;
		$this->fixRawJson($string);

		$json = json_decode($string, true);

		if ($json === null) {
			throw new HtmlParsingException($this->l->t('JSON cannot be decoded.'));
		}

		if ($json === false || $json === true || !is_array($json)) {
			throw new HtmlParsingException($this->l->t('No recipe was found.'));
		}

		if ($assumeSchemaContext) {
			$json = ['@context' => 'https://schema.org', '@graph' => isset($json[0]) ? $json : [$json]];
		}

		// Look through @graph field for recipe
		$this->mapGraphField($json);

		// Look for an array of recipes
		$this->mapArray($json);

		$this->mapMainEntity($json);

		if ($this->jsonService->isSchemaObject($json, 'Recipe', true, false)) {
			// Ensure the type of the object is never an array
			$this->checkForArrayType($json);

			// We found our recipe
			return $json;
		} else {
			// Continue with other approaches
		}

		//
		throw new HtmlParsingException($this->l->t('No recipe was found.'));
	}

	/**
	 * Fix any JSON issues before trying to decode it
	 *
	 * @param string $rawJson The JSON string to check and fix
	 */
	private function fixRawJson(string &$rawJson): void {
		$rawJson = $this->removeNewlinesInJson($rawJson);
	}

	/**
	 * Fix newlines in raw JSON string
	 *
	 * Some recipes have newlines inside quotes, which is invalid JSON. Fix this before continuing.
	 *
	 * @param string $rawJson The original string
	 * @return string The corrected JSON
	 */
	private function removeNewlinesInJson(string $rawJson): string {
		$ret = preg_replace('/\s+/', ' ', $rawJson);
		if (is_null($ret)) {
			throw new HtmlParsingException($this->l->t('Cannot combine whitespace characters.'));
		}
		return $ret;
	}

	/**
	 * Look for recipes in the JSON graph
	 *
	 * Some sites use the @graph property to define elements.
	 * This is a quick workaround to extract the corresponding recipe.
	 *
	 * @todo This only extracts the very first recipe in the graph and only that.
	 * It might be favorable to look further into the json objects.
	 * This might especially be true when the recipe uses links to external JSON objects
	 * (as specified by the standard).
	 * Then, it might become necessary to parse ALL objects in the graph in order to extract e.g.
	 * the instruction objects for a recipe.
	 *
	 * @param array $json The JSON object to check
	 */
	private function mapGraphField(array &$json) {
		if (isset($json['@graph']) && is_array($json['@graph'])) {
			// Sometimes the context is set once on the top level object for children to inherit
			$tmp = $this->searchForRecipeInArray($json['@graph'], $json['@context'] ?? null);

			if ($tmp !== null) {
				$json = $this->resolveReferences($tmp, $json['@graph']);
			}
		}
	}

	/**
	 * Replace image references like {"@id": "..."} in the recipe by the referenced objects of the graph
	 *
	 * @param array $recipe The recipe found in the graph
	 * @param array $graph The graph containing the referenced objects
	 * @return array The recipe with resolved image references
	 */
	private function resolveReferences(array $recipe, array $graph): array {
		if (!isset($recipe['image']) || !is_array($recipe['image'])) {
			return $recipe;
		}

		$nodes = array_column(array_filter($graph, fn ($node) => is_array($node) && isset($node['@id'])), null, '@id');
		$resolve = fn ($value) => is_array($value) && array_keys($value) === ['@id'] ? ($nodes[$value['@id']] ?? $value) : $value;

		$recipe['image'] = isset($recipe['image'][0]) ? array_map($resolve, $recipe['image']) : $resolve($recipe['image']);

		return $recipe;
	}

	/**
	 * Use the main entity of a page if it is a recipe
	 *
	 * @param array $json The JSON object to check
	 */
	private function mapMainEntity(array &$json) {
		if (isset($json['mainEntity']) && $this->jsonService->isSchemaObject($json['mainEntity'], 'Recipe', false, false)) {
			$entity = $json['mainEntity'];

			if (isset($json['@context']) && !isset($entity['@context'])) {
				$entity['@context'] = $json['@context'];
			}

			$json = $entity;
		}
	}

	/**
	 * Look for an array of recipes.
	 *
	 * Some sites return an array of JSON objects instead of a plain recipe object.
	 * This functions checks for an indexed array and searches in it for recipes.
	 *
	 * When an array of recipes is found, the first found recipe will be used and written over the
	 * input parameter.
	 * @param array $json The JSON object to inspect
	 */
	private function mapArray(array &$json) {
		if (isset($json[0])) {
			$tmp = $this->searchForRecipeInArray($json);

			if ($tmp !== null) {
				$json = $tmp;
			}
		}
	}

	/**
	 * Search for a recipe object in an array
	 * @param array $arr The array to search
	 * @param mixed $context The context inherited from the parent, if any
	 * @return array|NULL The found recipe or null if no recipe was found in the array
	 */
	private function searchForRecipeInArray(array $arr, $context = null): ?array {
		// Iterate through all objects in the array ...
		foreach ($arr as $item) {
			if (!is_array($item)) {
				continue;
			}

			if ($context !== null && !isset($item['@context'])) {
				$item['@context'] = $context;
			}

			$this->mapGraphField($item);
			$this->mapMainEntity($item);

			// ... looking for a recipe
			if ($this->jsonService->isSchemaObject($item, 'Recipe', true, false)) {
				// We found a recipe in the array, use it
				return $item;
			}
		}

		// No recipe was found
		return null;
	}

	/**
	 * Check if the JSON element is a schema.org object but malformed.
	 *
	 * This sets the '@type' entry to 'Recipe', correcting arrays and prefixed types.
	 *
	 * @param array $json The JSON object to parse
	 */
	private function checkForArrayType(array &$json) {
		$json['@type'] = 'Recipe';
	}
}
