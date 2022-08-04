<?php

function resetEnvironmentToBackup(string $name = 'main', bool $forceprint = false) {
	$output = [];
	$ret = -1;
	exec("./.github/actions/run-tests/reset-from-container.sh $name 2>&1", $output, $ret);
	if ($ret !== 0 || $forceprint) {
		echo "\nStandard output:\n";
		print_r($output);
		echo "Return value: $ret\n";
		if ($ret !== 0) {
			throw new Exception("Could not reset environment");
		}
	}
}

function runOCCCommand(array $args, bool $forceprint = false) {
	$output = [];
	$ret = -1;
	$params = join(' ', array_map(function ($x) {
		return escapeshellarg($x);
	}, $args));

	$cmd = "./.github/actions/run-tests/run-occ.sh $params 2>&1";

	exec($cmd, $output, $ret);
	if ($ret !== 0 || $forceprint) {
		echo "\nStandard output:\n";
		print_r($output);
		echo "Return value: $ret\n";
		if ($ret !== 0) {
			throw new Exception("Could not run OCC command");
		}
	}
}
