<?php

namespace OCA\Cookbook\Helper\HTMLParser;

use DOMNode;
use DOMXPath;
use OCP\IL10N;
use DOMDocument;
use DOMNodeList;
use OCA\Cookbook\Exception\HtmlParsingException;

/**
 * This class is an AbstractHtmlParser which tries to extract micro data from the HTML page.
 * @author Christian Wolf
 * @todo Nutrition data is missing
 * @todo Category needs checking
 * @todo Tools need to be imported
 */
class HttpMicrodataParser extends AbstractHtmlParser {
	/**
	 * @var \DOMXPath
	 */
	private $xpath;

	/**
	 * @var array
	 */
	private $recipe;

	public function __construct(IL10N $l10n) {
		parent::__construct($l10n);
	}

	public function parse(DOMDocument $document): array {
		$this->xpath = new DOMXPath($document);

		$selectorHttp = "//*[@itemtype='http://schema.org/Recipe']";
		$selectorHttps = "//*[@itemtype='https://schema.org/Recipe']";
		$selectorHttpWww = "//*[@itemtype='http://www.schema.org/Recipe']";
		$selectorHttpsWww = "//*[@itemtype='https://www.schema.org/Recipe']";
		$xpathSelector = "$selectorHttp | $selectorHttps | $selectorHttpWww | $selectorHttpsWww";

		$recipes = $this->xpath->query($xpathSelector);

		if (count($recipes) === 0) {
			throw new HtmlParsingException($this->l->t('No recipe was found.'));
		}

		$this->recipe = [
			'@type' => 'Recipe',
			'@context' => 'http://schema.org'
		];
		$this->parseRecipe($recipes[0]);

		return $this->recipe;
	}

	/**
	 * Parse a DOM node that represents a recipe
	 *
	 * @param DOMNode $recipeNode The DOM node to parse
	 */
	private function parseRecipe(DOMNode $recipeNode): void {
		$this->searchSimpleProperties($recipeNode, 'name');
		$this->searchSimpleProperties($recipeNode, 'keywords');
		$this->searchSimpleProperties($recipeNode, 'category');
		$this->searchSimpleProperties($recipeNode, 'recipeYield');

		$this->parseImage($recipeNode);
		$this->parseIngredients($recipeNode);
		$this->parseInstructions($recipeNode);

		$this->fixupInstructions($recipeNode);
	}

	/**
	 * Make one final desperate attempt at getting the instructions
	 * @param DOMNode $recipeNode The recipe node to use
	 */
	private function fixupInstructions(DOMNode $recipeNode): void {
		if (
			!isset($this->recipe['recipeInstructions']) ||
			!$this->recipe['recipeInstructions'] || sizeof($this->recipe['recipeInstructions']) < 1
		) {
			$this->recipe['recipeInstructions'] = [];

			$step_elements = $recipeNode->getElementsByTagName('p');

			foreach ($step_elements as $step_element) {
				if (!$step_element || !$step_element->nodeValue) {
					continue;
				}

				$this->recipe['recipeInstructions'][] = $step_element->nodeValue;
			}
		}
	}

	/**
	 * Search for images in the microdata of a recipe
	 * @param DOMNode $recipeNode The recipe to search within
	 * @return bool true, if a property was found
	 */
	private function parseImage(DOMNode $recipeNode): bool {
		return $this->searchMultipleProperties(
			$recipeNode,
			['image', 'images', 'thumbnail'],
			['src', 'content'],
			'image'
		);
	}

	/**
	 * Search for ingredients in the microdata of a recipe
	 * @param DOMNode $recipeNode The recipe to search within
	 * @return bool true, if a property was found
	 */
	private function parseIngredients(DOMNode $recipeNode): bool {
		return $this->searchMultipleProperties(
			$recipeNode,
			['recipeIngredient', 'ingredients'],
			['content'],
			'recipeIngredient'
		);
	}

	/**
	 * Search for instructions in the microdata of a recipe
	 * @param DOMNode $recipeNode The recipe to search within
	 * @return bool true, if a property was found
	 */
	private function parseInstructions(DOMNode $recipeNode): bool {
		return $this->searchMultipleProperties(
			$recipeNode,
			['recipeInstructions', 'instructions', 'steps', 'guide'],
			['content'],
			'recipeInstructions'
		);
	}

	/**
	 * Search for microdata properties under various names in the recipe and save as a list
	 *
	 * If a property can be named differently and found under different property names within
	 * the DOM tree, this method looks for all these options.
	 * It is similar to the searchSimpleProperties() method but allows to search for different
	 * names within the DOM tree. This can be useful when a property is superseeded and the code
	 * should still be backward compatible.
	 *
	 * @param DOMNode $recipeNode The node of the recipe to look for properties in
	 * @param array $properties The properties to look for one-by-one
	 * @param array $attributes The attributes that will contain the data
	 * @param string $dst The name of the property list in the internal structure
	 * @return bool true, if the property was found
	 */
	private function searchMultipleProperties(
		DOMNode $recipeNode,
		array $properties,
		array $attributes,
		string $dst
	): bool {
		foreach ($properties as $prop) {
			$entries = $this->searchChildEntries($recipeNode, $prop);

			try {
				$arrayObtained = $this->extractAttribute($entries, $attributes);

				if (count($arrayObtained) > 0) {
					$this->recipe[$dst] = $arrayObtained;
					return true;
				}
			} catch (AttributeNotFoundException $ex) {
				// Test with the next property name
				continue;
			}
		}

		return false;
	}

	/**
	 * Search for microdata properties of the recipe.
	 *
	 * Within the recipe node search for DOM entries that represent a property and assign the
	 * internal structure to their value.
	 *
	 * @param DOMNode $recipeNode The node of the recipe under which to look for properties
	 * @param string $property The property name to look for
	 * @return bool true, if the property was found
	 */
	private function searchSimpleProperties(DOMNode $recipeNode, string $property): bool {
		$ret = $this->searchMultipleProperties($recipeNode, [$property], ['content'], $property);

		// Extract array if only one entry was found.
		if ($ret && count($this->recipe[$property]) === 1) {
			$this->recipe[$property] = $this->recipe[$property][0];
		}

		return $ret;
	}

	/**
	 * Search for child entries that are representing a certain property
	 *
	 * @param DOMNode $recipeNode The root node to search in
	 * @param string $prop The name of the property to look for
	 * @return DOMNodeList A list of all found child nodes with the given property
	 */
	private function searchChildEntries(DOMNode $recipeNode, string $prop): DOMNodeList {
		return $this->xpath->query("//*[@itemprop='$prop']", $recipeNode);
	}

	/**
	 * Extract the value from an HTML attribute
	 *
	 * This method checks a set of notes if any of these nodes contain an attribute that can be used
	 * to extract some microdata. The nodes are iterated one-by-one. As soon as a match is found,
	 * the method returns the corresponding value and terminates.
	 *
	 * In each node each attribute is checked (in order of occurrence) if the node has such an attribute.
	 * If it has such an attribute that attribute is assumed to be the searched value and is used.
	 * If no attribute is found, the content of the node is checked and if it is non-empty, the content
	 * of the node is used instead.
	 *
	 * If no node contains an attribute with one of the given names and no content, an exception is
	 * thrown.
	 *
	 * @param DOMNodeList $nodes The list of all nodes to look for corresponding attributes
	 * @param array $attributes The attributes to check
	 * @throws AttributeNotFoundException If the property was not found in any node
	 * @return array The values of the property found
	 */
	private function extractAttribute(DOMNodeList $nodes, array $attributes): array {
		$foundEntries = [];

		/** @var $node \DOMElement */
		foreach ($nodes as $node) {
			try {
				$foundEntries[] = $this->extractSingeAttribute($node, $attributes);
			} catch (AttributeNotFoundException $ex) {
				continue;
			}
		}

		if (count($foundEntries) === 0) {
			throw new AttributeNotFoundException();
		} else {
			return $foundEntries;
		}
	}

	/**
	 * Checks if any of the given attributes is found on the given node.
	 *
	 * This can be used to extract a single attribute from the DOM tree.
	 * The attributes are evaluated first to last and the first found attribute is returned.
	 *
	 * @param DOMNode $node The  node to evaluate
	 * @param array $attributes The possible attributes to check
	 * @throws AttributeNotFoundException If none of the named attributes is found
	 * @return string The value of the attribute
	 */
	private function extractSingeAttribute(DOMNode $node, array $attributes): string {
		foreach ($attributes as $attr) {
			if ($node->hasAttribute($attr) && !empty($node->getAttribute($attr))) {
				return $node->getAttribute($attr);
			}
		}

		if (!empty(trim($node->textContent))) {
			return trim($node->textContent);
		}

		throw new AttributeNotFoundException();
	}
}
