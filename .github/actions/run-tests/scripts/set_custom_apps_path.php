<?php

require('/nextcloud/config/config.php');

if(!isset($CONFIG['apps_paths'])) {
	$CONFIG["apps_paths"] = [
		[
			"path"     => "/nextcloud/apps",
			"url"      => "/apps",
			"writable" => false,
		],
		[
			"path"     => "/nextcloud/custom_apps",
			"url"      => "/custom_apps",
			"writable" => true,
		]
	];

} else {
	$found = false;
	
	foreach($CONFIG['apps_paths'] as $pc) {
		if($pc['path'] === '/nextcloud/custom_apps') {
			$found = true;
			break;
		}
	}
	
	if(!$found) {
		$CONFIG['apps_paths'][] = [
			"path"     => "/nextcloud/custom_apps",
			"url"      => "/custom_apps",
			"writable" => true,
		];
	}
}


$dump = '<?php' . "\n" . '$CONFIG = ' . var_export($CONFIG, true) . ";\n";

file_put_contents('/nextcloud/config/config.php', $dump);

