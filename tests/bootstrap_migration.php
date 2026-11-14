<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once __DIR__ . '/bootstrap_helper.php';

// PHPUnit reruns this bootstrap in each isolated test process; tests reset cookbook tables themselves.
const ENV_RESET_DONE = 'COOKBOOK_MIGRATION_ENV_RESET_DONE';
$isFirstRun = getenv(ENV_RESET_DONE) === false;
if ($isFirstRun) {
	resetEnvironmentToBackup('plain', false);
	putenv(ENV_RESET_DONE . '=1');
}

require_once __DIR__ . '/../../../tests/bootstrap.php';

// Fix for "Autoload path not allowed: .../cookbook/tests/testcase.php"
\OC_App::loadApp('cookbook');
