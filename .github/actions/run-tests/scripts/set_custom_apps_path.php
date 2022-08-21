<?php

require('/var/www/html/config/config.php');

if(!isset($CONFIG['apps_paths'])) {
	$CONFIG["apps_paths"] = [
		[
			"path"     => "/var/www/html/apps",
			"url"      => "/apps",
			"writable" => false,
		],
		[
			"path"     => "/var/www/html/custom_apps",
			"url"      => "/custom_apps",
			"writable" => true,
		]
	];

} else {
	$found = false;
	
	foreach($CONFIG['apps_paths'] as $pc) {
		if($pc['path'] === '/var/www/html/custom_apps') {
			$found = true;
			break;
		}
	}
	
	if(!$found) {
		$CONFIG['apps_paths'][] = [
			"path"     => "/var/www/html/custom_apps",
			"url"      => "/custom_apps",
			"writable" => true,
		];
	}
}


$dump = '<?php' . "\n" . '$CONFIG = ' . var_export($CONFIG, true) . ";\n";

file_put_contents('/var/www/html/config/config.php', $dump);

