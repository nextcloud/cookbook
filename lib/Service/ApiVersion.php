<?php

namespace OCA\Cookbook\Service;

class ApiVersion {
	public static function getApiVersion(): array {
		return [
			'epoch' => 0,
			'major' => 1,
			'minor' => 2
		];
	}

	public function getAppVersion(): array {
		return [0, 11, 6]; /* VERSION_TAG do not change this line manually */
	}
}
