<?php

namespace OCA\Cookbook\AppInfo;

use OCA\Cookbook\Service\ApiVersion;
use OCP\Capabilities\ICapability;

class Capabilities implements ICapability {
	public function __construct(
		private ApiVersion $apiVersion,
	) {
	}

	#[\Override]
	public function getCapabilities() {
		return [
			'cookbook' => [
				'version' => $this->apiVersion->getAppVersion(),
			]
		];
	}
}
