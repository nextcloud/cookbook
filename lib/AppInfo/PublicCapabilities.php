<?php

namespace OCA\Cookbook\AppInfo;

use OCA\Cookbook\Service\ApiVersion;
use OCP\Capabilities\IPublicCapability;

class PublicCapabilities implements IPublicCapability {
	public function __construct(
		private ApiVersion $apiVersion,
	) {
	}

	#[\Override]
	public function getCapabilities() {
		return [
			'cookbook' => [
				'api-version' => $this->apiVersion->getAPIVersion(),
			]
		];
	}
}
