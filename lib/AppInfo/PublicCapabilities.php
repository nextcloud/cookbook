<?php

namespace OCA\Cookbook\AppInfo;

use OCP\Capabilities\IPublicCapability;

class PublicCapabilities implements IPublicCapability {
	#[\Override]
	public function getCapabilities() {
		return [
			'cookbook' => [
				'api-version' => [
					0,1,2
				]
			]
		];
	}
}
