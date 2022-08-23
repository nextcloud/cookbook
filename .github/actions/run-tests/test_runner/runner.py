
import test_runner.ci_printer as l
import test_runner.proc as p

import os

class TestRunner:
	def __init__(self):
		pass

	def __buildRunCmd(self, args, subArgs):
		cmd = ['docker-compose', 'run', '--rm', 'dut']

		if args.run_unit_tests:
			cmd.append('--run-unit-tests')
		
		if args.run_integration_tests:
			cmd.append('--run-integration-tests')
		
		if args.run_migration_tests:
			cmd.append('--run-migration-tests')
		
		if args.extract_code_coverage:
			cmd.append('--create-coverage-report')
		
		if args.install_composer_deps:
			cmd.append('--install-composer-deps')
		
		if args.build_npm:
			cmd.append('--build-npm')
		
		if args.run_code_checker:
			cmd.append('--run-code-checker')

		cmd.append('--')

		return cmd + subArgs

	def __getDebugMode(self, args):
		modes = []

		if args.debug:
			modes.append('debug')
		if args.enable_tracing:
			modes.append('trace')
		if args.enable_profiling:
			modes.append('profile')
		
		return ",".join(modes)

	def runTests(self, args, subArgs, db):
		cmd = self.__buildRunCmd(args, subArgs)
		env = os.environ
		env['CI'] = 'true' if args.ci else 'false'
		env['RUNNER_UID'] = str(os.getuid())
		env['RUNNER_GID'] = str(os.getgid())
		env['DEBUG_MODE'] = self.__getDebugMode(args)
		env['DEBUG_PORT'] = args.debug_port[0]
		env['DEBUG_HOST'] = args.debug_host[0]
		env['DEBUG_UPON_ERROR'] = 'yes' if args.debug_upon_error else 'default'
		env['DEBUG_START_MODE'] = args.debug_start_with_request[0]
		env['DEBUG_TRACE_FORMAT'] = args.trace_format[0]
		env['XDEBUG_LOG_LEVEL'] = args.xdebug_log_level[0]
		env['QUICK_MODE'] = 'y' if args.quick else 'n'
		env['INPUT_DB'] = db

		sp = p.pr.run(cmd, env=env)

		l.logger.printSetOutput('silentFail', 'false')
		if sp.returncode != 0:
			if args.install_untested:
				l.logger.printWarning('Failed testing with setting --install-untested')
				l.logger.printSetOutput('silentFail', 'true')
				return 0
			else:
				return sp.returncode
		else:
			return 0

