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
}
