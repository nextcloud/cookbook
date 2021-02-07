<?php

require_once __DIR__ . '/../../../tests/bootstrap.php';

// Fix for "Autoload path not allowed: .../cookbook/tests/testcase.php"
\OC_App::loadApp('cookbook');

function resetEnvironmentToBackup(string $name = 'default') {
	exec("./.github/actions/run-tests/reset-from-container.sh $name 2>&1", $output, $ret);
	if ($ret !== 0) {
		echo "\nStandard output:\n";
		print_r($output);
		echo "Return value: $ret\n";
		throw new Exception("Could not reset environment");
	}
}
