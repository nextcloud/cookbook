<?php

namespace OCA\Cookbook\Service;

/**
 * A collection of useful functions to handle schema.org JSON data
 *
 * @author Christian Wolf <github@christianwolf.email>
 *
 */
class JsonService {
	/**
	 * Check if an object is a JSON representation of a schema.org object
	 *
	 * The type of the object can be optionally checked using the second parameter.
	 *
	 * @param mixed $obj The object to check
	 * @param string $type The type to check for. If null or '' no type check is performed
	 * @param bool $checkContext If true, check for a present context entry
	 * @param bool $uniqueType If false, also accept JSON objects that contain multiple types as @type.
	 * @return bool true, if $obj is an object and optionally satisfies the type check
	 */
	public function isSchemaObject($obj, string $type = null, bool $checkContext = true, bool $uniqueType = true): bool {
		if (! is_array($obj)) {
			// Objects must bve encoded as arrays in JSON
			return false;
		}

		if ($checkContext) {
			if (!isset($obj['@context']) || ! preg_match('@^https?://schema\.org/?$@', $obj['@context'])) {
				// We have no correct context property
				return false;
			}
		}

		if (!isset($obj['@type'])) {
			// Objects must have a property @type
			return false;
		}

		// We have an object

		if ($type === null || $type === '') {
			// No typecheck was requested. So return true
			return true;
		}

		if (is_array($obj['@type'])) {
			if ($uniqueType) {
				if (count($obj['@type']) === 1 && $obj['@type'][0] === $type) {
					return true;
				}
				return false;
			}

			$foundTypes = array_filter($obj['@type'], function ($x) use ($type) {
				return trim($x) === $type;
			});

			return count($foundTypes) > 0;
		}

		// Check if type matches
		return (strcmp($obj['@type'], $type) === 0);
	}

	/**
	 * Check if $obj is a schema.org object and contains a named property.
	 *
	 * @param mixed $obj The object to check
	 * @param string $property The name of the property to check for
	 * @return bool true, if $obj is a object and has the property given
	 */
	public function hasProperty($obj, string $property): bool {
		if (!$this->isSchemaObject($obj, null, false)) {
			return false;
		}

		return array_key_exists($property, $obj);
	}
}
