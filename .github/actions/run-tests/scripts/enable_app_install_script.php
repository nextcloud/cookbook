<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

require('/var/www/html/config/config.php');

if (!isset($CONFIG['app_install_overwrite'])) {
	$CONFIG['app_install_overwrite'] = array();
}

if (! in_array('cookbook', $CONFIG['app_install_overwrite'])) {
	$CONFIG['app_install_overwrite'][] = 'cookbook';
}

$dump = '<?php' . "\n" . '$CONFIG = ' . var_export($CONFIG, true) . ";\n";

file_put_contents('/var/www/html/config/config.php', $dump);
