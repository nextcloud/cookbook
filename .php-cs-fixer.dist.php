<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

use Nextcloud\CodingStandard\Config;

$config = new Config();
$config
	->getFinder()
	->ignoreVCSIgnored(true)
	->exclude('build')
	->exclude('l10n')
//	->notPath('lib/Vendor')
	->exclude('src')
	->exclude('node_modules')
	->exclude('vendor')
	->exclude('.github')
	->in(__DIR__);
return $config;

