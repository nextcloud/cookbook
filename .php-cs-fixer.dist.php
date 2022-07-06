<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

use Nextcloud\CodingStandard\Config;

class CookbookConfig extends Config {
	public function __construct($name = 'default') {
		parent::__construct($name);
	}

	public function getRules() : array {
		$parentRules = parent::getRules();
		$additionalRules = [
			'phpdoc_add_missing_param_annotation' => true,
			'phpdoc_indent' => true,
			'phpdoc_no_empty_return' => true,
			'phpdoc_scalar' => true,
			'phpdoc_single_line_var_spacing' => true,
			'phpdoc_var_without_name' => true
		];
		$ret = array_merge(['@PSR12' => true], $parentRules, $additionalRules);
		// print_r($ret);
		return $ret;
	}
}

$config = new CookbookConfig();
$finder = $config
	->getFinder()
	// ->ignoreVCSIgnored(true)
	->exclude('build')
	->exclude('l10n')
	->exclude('src')
	->exclude('node_modules')
	->exclude('vendor')
	->exclude('.github')
	->in(__DIR__ );

$config->setFinder($finder);

// foreach($finder as $f) {
// 	print_r($f);
// }

// print_r($config);

return $config;

