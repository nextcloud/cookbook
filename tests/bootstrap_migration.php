<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once __DIR__ . '/bootstrap_helper.php';

// Runs once per isolated test process: restore fully only in the parent, tests reset cookbook tables themselves.
if (getenv('COOKBOOK_MIGRATION_ENV_RESET') === false) {
	resetEnvironmentToBackup('plain', false);
	putenv('COOKBOOK_MIGRATION_ENV_RESET=1');
}

require_once __DIR__ . '/../../../tests/bootstrap.php';

// Fix for "Autoload path not allowed: .../cookbook/tests/testcase.php"
\OC_App::loadApp('cookbook');
