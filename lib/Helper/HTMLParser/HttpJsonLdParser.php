<?php

namespace OCA\Cookbook\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCP\IL10N;
use OCA\Cookbook\Service\JsonService;

/**
 * This class is an AbsractHtmlParser that tries to extract a JSON+LD script from the HTML page.
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
	
	public function parse(\DOMDocument $document): array {
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
		$string = $node->nodeValue;
		
		$this->fixRawJson($string);
		
		$json = json_decode($string, true);
		
		if ($json === null) {
			throw new HtmlParsingException($this->l->t('JSON cannot be decoded.'));
		}
		
		if ($json === false || $json === true || ! is_array($json)) {
			throw new HtmlParsingException($this->l->t('No recipe was found.'));
		}
		
		// Look through @graph field for recipe
		$this->mapGraphField($json);
		
		// Look for an array of recipes
		$this->mapArray($json);
		
		if ($this->jsonService->isSchemaObject($json, 'Recipe')) {
			// We found our recipe
			return $json;
		} else {
			throw new HtmlParsingException($this->l->t('No recipe was found.'));
		}
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
		return preg_replace('/\s+/', ' ', $rawJson);
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
			$tmp = $this->searchForRecipeInArray($json[@graph]);
			
			if ($tmp !== null) {
				$json = $tmp;
				return;
			}
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
	 * @param array $json The JSON object to insprect
	 */
	private function mapArray(array &$json) {
		if (isset($json[0])) {
			$tmp = $this->searchForRecipeInArray($json);
			
			if ($tmp !== null) {
				$json = $tmp;
				return;
			}
		}
	}
	
	/**
	 * Search for a recipe object in an array
	 * @param array $arr The array to search
	 * @return array|NULL The found recipe or null if no recipe was found in the array
	 */
	private function searchForRecipeInArray(array $arr): ?array {
		// Iterate through all objects in the array ...
		foreach ($arr as $item) {
			// ... looking for a recipe
			if ($this->jsonService->isSchemaObject($item, 'Recipe')) {
				// We found a recipe in the array, use it
				return $item;
			}
		}
		
		// No recipe was found
		return null;
	}
}
