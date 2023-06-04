import argparse
import sys
import os

def parseArguments():
	parser = argparse.ArgumentParser()

	parser.add_argument('-v', '--verbose', action='store_true', help='Do not hide the output of intermediate commands')
	parser.add_argument('--debug-python', action='store_true', help='Activate the python debugger to develop the test environments.')

	group = parser.add_argument_group('Docker image management', 'Manage the docker images involved in the test routines.')

	group.add_argument('--pull', action='store_true', help='Force pulling of the latest images from docker hub.')
	group.add_argument('--create-images', '-c', action='store_true', help='Force creation of custom docker images locally used for testing')
	group.add_argument('--pull-php-base-image', action='store_true', help='Pull the PHP base image additionally. This makes rebuilding the test images more likely.')
	group.add_argument('--push-images', action='store_true', help='Push images to docker. Not yet working')

	group = parser.add_argument_group('Management of helper containers', 'These parameters help with managing auxiliary containers needed for the tests.')

	group.add_argument('--start-helpers', action='store_true', help='Start helper containers (database, http server)')
	group.add_argument('--shutdown-helpers', action='store_true', help='Shut down all containers running')

	group = parser.add_argument_group('Nextcloud server configuration', '''These settings allow to set the nextcloud server version that is used as a test environment with the app\'s code.
	Please note that the installation of a new version is taking a significant amount of time.
	Thus, the environment is installed only once and then dumps are generated to allow fast resetting.''')

	group.add_argument('--create-fixture', nargs=2, metavar=('NAME', 'BRANCH'), help='Setup a development environment in current folder. BRANCH dictates the branch of the server to use (e.g. stable20). The NAME dictates the name of the fixture.')
	group.add_argument('--overwrite-fixture', action='store_true', help='Allow to overwrite a backup of an environment')
	group.add_argument('--fixture-description', nargs=1, metavar='DESC', help='The human-readable description of the fixture to be created.')
	group.add_argument('--use-db-dump', action='store_true', help='Use the SQL dumps for restoring of the database')
	group.add_argument('--list-fixtures', action='store_true', help='List all present fixtures and exit the program')
	group.add_argument('--drop-fixture', nargs=1, metavar='NAME', help='Remove a single fixture from the list of fixtures')
	group.add_argument('--activate-fixture', nargs=1, metavar='NAME', help='Activate the fixture NAME for all future tests')
	group.add_argument('--force-restore', action='store_true', help='Restore the fixture before running the tests. This might recover from bad tests.')

	group = parser.add_argument_group('Running of tests', '''These options specify what tests should be carried out for the current run.
	You can specify groups of tests (unit, integration, migration) as well as additional tests.
	Additional reports can be generated in order to allow for code coverage analysis.''')

	group.add_argument('--run-unit-tests', '-u', action='store_true', help='Run only the unit tests')
	group.add_argument('--run-integration-tests', '-i', action='store_true', help='Run only the integration tests')
	group.add_argument('--run-migration-tests', '-m', action='store_true', help='Run only the migration tests')

	group.add_argument('--extract-code-coverage', '-x', action='store_true', help='Output the code coverage reports into the folder volumes/coverage/.')
	group.add_argument('--keep-code-coverage', action='store_true', help='Normally, the last code coverage is removed to avoid filling up the disk. This flag keeps the old one.')
	group.add_argument('--install-composer-deps', action='store_true', help='Install composer dependencies')

	group.add_argument('--build-npm', action='store_true', help='Install and build js packages')

	group.add_argument('--quick', '-q', action='store_true', help='Test in quick mode. This will not update the permissions on successive (local) test runs.')

	group = parser.add_argument_group('Debugging the tests', '''It is possible to debug the test runs using xdebug.
	This might be useful if the outcome of the tests is not as expected.
	You are responsible to set up your IDE appropriately to allow for the connection from the PHP server to succeed.
	Other options by the xdebug framework like tracing and profiling can be enabled as well.
	Output of these functions will be under volumes/output present.''')

	group.add_argument('--debug', '-d', action='store_true', help='Enable step debugging during the testing')
	group.add_argument('--enable-tracing', action='store_true', help='Enable the tracing feature of xdebug')
	group.add_argument('--enable-profiling', action='store_true', help='Enable the profiling function of xdebug')

	group.add_argument('--debug-port', nargs=1, metavar='PORT', default=['9000'], help='Select the port on the host machine to attach during debugging sessions using xdebug (default 9000)')
	group.add_argument('--debug-host', nargs=1, metavar='HOST', default=['172.17.0.1'], help='Host to connect the debugging session to (default to local docker host)')
	group.add_argument('--debug-upon-error', action='store_true', help='Enable the debugger in case of an error (see xdebug\'s start_upon_error configuration)')
	group.add_argument('--debug-start-with-request', nargs=1, metavar='MODE', default=['yes'], help='Set the starting mode of xdebug to <MODE> (see xdebug\'s start_with_request configuration, \'yes\' by default)')
	parser.add_argument('--xdebug-log-level', nargs=1, metavar='LEVEL', default=['3'], help='Set the log level of xdebug to <LEVEL>, defaults to 3')
	group.add_argument('--trace-format', nargs=1, metavar='FORMAT', default=['1'], help='Set the trace format to the <FORMAT> (see xdebug\'s trace_format configuration, default 1)')

	group = parser.add_argument_group('Batch commands', 'These commands are intended as shorthands for some other commands to allow for faster runs.')

	# group.add_argument('--prepare', action='store_true', help='Prepare the system for running the unit tests. This is a shorthand for --pull --create-images-if-needed --start-helpers --setup-environment <BRANCH> --create-env-dump --create-plain-dump plain')
	group.add_argument('--run-default-tests', action='store_true', help='Run both unit as well as integration tests and code checking')
	group.add_argument('--run-all-tests', action='store_true', help='Run all tests present')
	group.add_argument('--run', action='store_true', help='Run the unit tests themselves. This is a shorthand for --restore-env-dump --run-tests --extract-code-coverage')

	group = parser.add_argument_group('Environment settings', '''There are several configurations that define the environment in which the tests are running.
	This includes for example the type of the database or the used PHP version.
	In the legacy version this was solved using environment variables.
	In the new version, it is possible to set it both via environment and via command lien options.
	The command line options take precedence.''')

	group.add_argument('--input-db', nargs=1, choices=['mysql', 'pgsql', 'sqlite'], default=[os.environ.get('INPUT_DB', 'mysql')], help='Defines which database is used for the integration and migration tests. Defaults to mysql.')
	group.add_argument('--php-version', nargs=1, default=[os.environ.get('PHP_VERSION', '8.1')], help='Defines the PHP version to use, e.g. 7.4, 8. Defaults to 8.1.')
	group.add_argument('--http-server', nargs=1, choices=['apache', 'nginx'], default=[os.environ.get('HTTP_SERVER', 'apache')], help='Defines the HTTP daemon to use. Defaults to apache.')
	group.add_argument('--ci', action='store_true', help='If the script is run in CI environment')
	group.add_argument('--install-untested', action='store_true', help='Allows to install the app even though it is not marked as supported')

	# print(sys.argv)

	if '--' in sys.argv:
		index = sys.argv.index('--')
		scriptArgs = sys.argv[1:index]
		passingArgs = sys.argv[index+1:]
	else:
		scriptArgs = sys.argv[1:]
		passingArgs = []

	# print(scriptArgs)
	# print(passingArgs)

	args = parser.parse_args(scriptArgs)

	if not args.ci and os.environ.get('CI', 'false') == 'true':
		args.ci = True
	
	if args.run:
		args.extract_code_coverage = True
		args.run_all_tests = True
	
	if args.run_all_tests:
		args.run_default_tests = True
		args.run_migration_tests = True
	
	if args.run_default_tests:
		args.run_unit_tests = True
		args.run_integration_tests = True

	return (args, passingArgs)


