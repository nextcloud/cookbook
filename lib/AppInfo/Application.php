<?php

namespace OCA\Cookbook\AppInfo;

use OCA\Cookbook\Controller\v1\ConfigController;
use OCA\Cookbook\Controller\v1\MainController;
use OCA\Cookbook\Controller\v1\RecipeController;
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

			$context->registerServiceAlias('ConfigV1Controller', ConfigController::class);
			$context->registerServiceAlias('MainV1Controller', MainController::class);
			$context->registerServiceAlias('RecipeV1Controller', RecipeController::class);
			$context->registerServiceAlias('RecipeLegacyController', RecipeController::class);
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
