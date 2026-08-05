<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

require_once __DIR__ . '/bootstrap_helper.php';

require_once __DIR__ . '/../../../tests/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Fix for "Autoload path not allowed: .../cookbook/tests/testcase.php"
\OC_App::loadApp('cookbook');
