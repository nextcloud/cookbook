<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Fix the timestamp of the dates created and modified for recipe stubs
 *
 * This mainly handles the missing `T` in the date between date and time to be ISO confirm.
 */
class TimestampFixFilter extends AbstractJSONFilter {
	public function apply(array &$json): bool {
		$changed = false;
		foreach(['dateCreated', 'dateModified'] as $key) {
			if(isset($json[$key]) && $json[$key]) {
				$json[$key] = $this->handleTimestamp($json[$key], $changed);
			}
		}
		return $changed;
	}

	private function handleTimestamp(string $value, bool &$changed): string {
		$pattern = '/^([0-9]{4}-[0-9]{2}-[0-9]{2}) +(.*)/';
		$replacement = '${1}T${2}';
		$nv = preg_replace($pattern, $replacement, $value);

		if($nv !== $value) {
			$changed = true;
		}
		return $nv;
	}

}
