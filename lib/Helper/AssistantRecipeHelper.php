<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\InvalidAssistantResponseException;
use OCA\Cookbook\Helper\Filter\JSON\JSONFilter;
use OCP\IL10N;

/**
 * Helper to exchange recipes with the Nextcloud Assistant.
 *
 * It creates the prompt that is sent to the text processing task and parses the answer of the
 * assistant back into the recipe fields of the editor.
 */
class AssistantRecipeHelper {
	/**
	 * The recipe fields the assistant may change, mapped to the value used if a field is missing.
	 */
	private const EDITABLE_FIELDS = [
		'name' => '',
		'description' => '',
		'recipeCategory' => '',
		'keywords' => '',
		'prepTime' => '',
		'cookTime' => '',
		'totalTime' => '',
		'recipeYield' => null,
		'tool' => [],
		'recipeIngredient' => [],
		'recipeInstructions' => [],
		'nutrition' => [],
	];

	/** @var IL10N */
	private $l;
	/** @var JSONFilter */
	private $jsonFilter;

	public function __construct(IL10N $l, JSONFilter $jsonFilter) {
		$this->l = $l;
		$this->jsonFilter = $jsonFilter;
	}

	/**
	 * Reduce a recipe to the fields that are sent to the assistant.
	 *
	 * Technical fields like the id or the image are never handed to the assistant.
	 *
	 * @param array $recipe The recipe as present in the editor
	 * @return array The fields of the recipe that may be changed by the assistant
	 */
	public function extractEditableFields(array $recipe): array {
		$fields = array_merge(self::EDITABLE_FIELDS, array_intersect_key($recipe, self::EDITABLE_FIELDS));
		// The editor sends an empty string for an unknown yield; the yield filter would turn that into 1 serving
		if ($fields['recipeYield'] === '') {
			$fields['recipeYield'] = null;
		}
		return $fields;
	}

	/**
	 * Build the prompt that is sent to the assistant
	 *
	 * @param array $recipe The recipe as present in the editor
	 * @param string $instruction The instruction of the user, e.g., "Translate the recipe to English"
	 * @return string The complete prompt for the text processing task
	 */
	public function buildPrompt(array $recipe, string $instruction): string {
		$json = json_encode(
			$this->extractEditableFields($recipe),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
		);

		$lines = [
			'You are a helpful assistant that edits cooking recipes.',
			'Below you find a recipe as a JSON object and an instruction from the user.',
			'Apply the instruction to the recipe and return the complete updated recipe.',
			'',
			'Rules:',
			'- Answer with a single JSON object only. Do not add any explanation, markdown or code fences.',
			'- Keep exactly the same keys and the same data types as in the given JSON object.',
			'- Only change the content that is necessary to follow the instruction. Keep all other values untouched.',
			'- "keywords" is a single string of comma-separated keywords.',
			'- "recipeYield" is the number of servings or null if unknown.',
			'- "prepTime", "cookTime" and "totalTime" are ISO 8601 durations (e.g. "PT1H30M") or empty strings.',
			'- "tool", "recipeIngredient" and "recipeInstructions" are lists of strings, one entry per item or step.',
			'- "nutrition" is an object mapping nutrition keys to strings.',
			'',
			'Instruction:',
			$instruction,
			'',
			'Recipe:',
			$json,
		];

		return implode("\n", $lines);
	}

	/**
	 * Parse the answer of the assistant into the recipe fields of the editor
	 *
	 * The fields are normalized by the same filters that are used when a recipe is saved.
	 * Only fields present in the answer are returned.
	 *
	 * @param string $output The raw text answer of the assistant
	 * @return array The updated recipe fields
	 * @throws InvalidAssistantResponseException if the answer does not contain a valid recipe
	 */
	public function parseResponse(string $output): array {
		$data = json_decode($this->extractJson($output), true);

		if (!is_array($data) || array_intersect_key($data, self::EDITABLE_FIELDS) === []) {
			throw new InvalidAssistantResponseException(
				$this->l->t('The answer of the assistant could not be interpreted as a recipe.')
			);
		}

		// The filters expect a complete recipe, so fill in the missing fields before filtering
		try {
			$recipe = $this->jsonFilter->apply($this->extractEditableFields($data));
		} catch (\Throwable $ex) {
			throw new InvalidAssistantResponseException(
				$this->l->t('The answer of the assistant could not be interpreted as a recipe.'),
				0,
				$ex
			);
		}

		return array_intersect_key($recipe, self::EDITABLE_FIELDS, $data);
	}

	/**
	 * Extract the JSON object from the answer of the assistant
	 *
	 * Language models tend to wrap the answer in markdown code fences or to add some prose around it.
	 * This method cuts out the outermost JSON object.
	 *
	 * @param string $output The raw answer
	 * @return string The part of the answer that most probably is the JSON object
	 */
	private function extractJson(string $output): string {
		$start = strpos($output, '{');
		$end = strrpos($output, '}');

		if ($start === false || $end === false || $end < $start) {
			return '';
		}

		return substr($output, $start, $end - $start + 1);
	}
}
