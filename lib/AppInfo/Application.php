<?php

namespace OCA\Cookbook\AppInfo;

use OCA\Cookbook\Search\Provider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Util;

// IBootstrap requies NC >= 20
// Remove conditional once we end support for NC 19
if (Util::getVersion()[0] >= 20) {
	class Application extends App implements IBootstrap {
		public const APP_ID = 'cookbook';
		
		public function __construct(array $urlParams = []) {
			parent::__construct(self::APP_ID, $urlParams);
		}

		public function register(IRegistrationContext $context): void {
			$context->registerSearchProvider(Provider::class);
		}

		public function boot(IBootContext $context): void {
		}
	}
} else {
	class Application extends App {
		public const APP_ID = 'cookbook';
		
		public function __construct(array $urlParams = []) {
			parent::__construct(self::APP_ID, $urlParams);
		}
	}
}
