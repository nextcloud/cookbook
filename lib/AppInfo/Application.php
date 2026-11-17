<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\AppInfo;

use OCA\Cookbook\Search\Provider;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'cookbook';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		$context->registerSearchProvider(Provider::class);
		$context->registerCapability(PublicCapabilities::class);
		$context->registerCapability(Capabilities::class);
		// Log serializer hides these args and scrubs the same values (e.g. recipe['url']) from all other trace frames
		$context->registerSensitiveMethods(RecipeService::class, ['downloadRecipe']);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
