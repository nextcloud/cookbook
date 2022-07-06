<?php

namespace OCA\Cookbook\Helper\Filter\DB;

use DateTime;
use DateTimeImmutable;
use OCA\Cookbook\Helper\Filter\AbstractRecipeFilter;
use OCP\Files\File;

/**
 * Ensure the dates of a recipe are valid
 * 
 * This filter will update the recipe to have both valid dateCreated and dateModified.
 * If the dates are given in correct format, nothing is changed.
 * 
 * If only the dateModified is given, the dateCreated is set to the same value.
 * 
 * If neither is given, the file modification time of the JSON file is taken into account.
 */
class RecipeDatesFilter implements AbstractRecipeFilter {

	private const DATE_CREATED = 'dateCreated';
	private const DATE_MODIFIED = 'dateModified';

	private const PATTERN_DATE = '\d{4}-\d{2}-\d{2}';
	private const PATTERN_TIME = '\d{1,2}:\d{2}:\d{2}(?:\.\d+)?';

	/** @var string */
	private $patternDate;

	public function __construct()
	{
		$this->patternDate = join('|', [
			'^' . self::PATTERN_DATE . '$',
			'^' . self::PATTERN_DATE . 'T' . self::PATTERN_TIME . '$'
		]);
	}

    public function apply(array &$json, File $recipe): bool {
		$ret = false;

		// First ensure the entries are present in general
		$this->ensureEntryExists($json, self::DATE_CREATED, $ret);
		$this->ensureEntryExists($json, self::DATE_MODIFIED, $ret);

		// Ensure the date formats are valid
		$this->checkDateFormat($json, self::DATE_CREATED, $ret);
		$this->checkDateFormat($json, self::DATE_MODIFIED, $ret);

		if(is_null($json['dateCreated'])) {
			if (is_null($json['dateModified'])) {
				// No dates have been set. Fall back to time of file
				$json['dateCreated'] = $this->getTimeFromFile($recipe);
				$ret = true;
			} else {
				// Copy over the modification time to the creation time
				$json['dateCreated'] = $json['dateModified'];
				$ret = true;
			}
		}
		/*
		The else case is not considered:
			If only the creation time is given, this is a valid recipe (no modifications so far).
			If both are given, no problem is present.
		*/

		return $ret;
	}

	private function getTimeFromFile(File $file): string {
		$timestamp = $file->getCreationTime();
		if($timestamp === 0) {
			$timestamp = $file->getUploadTime();
		}
		if($timestamp === 0) {
			$timestamp = $file->getMTime();
		}

		return $this->getDateFromTimestamp($timestamp);
	}
	
	private function getDateFromTimestamp(int $timestamp): string {
		$date = new DateTime();
		$date->setTimestamp($timestamp);
	
		return $date->format(DateTime::ISO8601);
	}

	private function ensureEntryExists(array &$json, string $name, bool &$ret) {
		if(!array_key_exists($name, $json)) {
			$json[$name] = null;
			$ret = true;
		}
	}

	private function checkDateFormat(array &$json, string $name, bool &$ret) {
		if($json[$name] === null) {
			return;
		}

		// Check for valid date format
		if(preg_match('/' . $this->patternDate . '/', $json[$name]) === 1) {
			return;
		}

		// Last desperate approach: Is it a timestamp?
		if(preg_match('/^\d+$/', $json[$name])) {
			if($json[$name] > 0) {
				$json[$name] = $this->getDateFromTimestamp($json[$name]);
				$ret = true;
				return;
			}
		}

		// We cannot read the format. Removing it from teh recipe
		$json[$name] = null;
		$ret = true;
		return;
	}
}
