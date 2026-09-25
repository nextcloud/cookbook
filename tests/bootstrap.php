<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

use OCP\App\IAppManager;
use OCP\Server;

require_once __DIR__ . '/bootstrap_helper.php';

define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../lib/base.php';
require_once __DIR__ . '/../../../tests/autoload.php';

require_once __DIR__ . '/../vendor/autoload.php';

Server::get(IAppManager::class)->loadApp('cookbook');
