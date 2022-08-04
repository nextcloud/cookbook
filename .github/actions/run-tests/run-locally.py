#!/usr/bin/env python

import test_runner
import test_runner.ci_printer as l

(args, subArgs) = test_runner.argument_parser.parseArguments()

# print(args)

test_runner.ci_printer.logger = test_runner.ci_printer.CILogger(args.ci)

timer = test_runner.timer.Timer()
subTimer = test_runner.timer.Timer()

env = test_runner.test_env.Environment()

dumpManager = test_runner.dumps.DumpsManager()
currentFixture = test_runner.dumps.CurrentFixture()

runner = test_runner.runner.TestRunner()
subfixture = test_runner.dumps.SubFixture()
rotation = test_runner.coverate_rotation.CoverageRotation()

if args.list_fixtures:
	dumpManager.listFixtures()
	exit(0)

env.ensureFolderStructureExists()

dumpManager.validate()
dumpManager.init()

test_runner.docker_management.handleDockerImages(args)

if args.drop_fixture is not None:
	l.logger.startGroup('Dropping fixture')
	timer.tic()
	fixture = test_runner.dumps.Fixture(args.drop_fixture[0])
	fixture.drop()
	l.logger.endGroup()
	timer.toc()

currentFixture.validate()

if args.create_fixture is not None:
	l.logger.startGroup('Create fixture')
	timer.tic()
	fixture = test_runner.dumps.Fixture(args.create_fixture[0])

	desc = args.fixture_description[0] if args.fixture_description is not None else None
	
	if args.input_db[0] == 'sqlite':
		sql_type = 'none'
	elif args.use_db_dump:
		sql_type = 'dump'
	else:
		sql_type = 'sync'
	
	db = args.input_db[0]

	fixtureConfig = test_runner.config.FixtureConfig()
	fixtureConfig.parseArgs(args)

	fixturePath = fixture.prepareFolders(fixtureConfig, args.overwrite_fixture)

	subTimer.tic()
	env.prepareBasicEnvironment(db, args.create_fixture[1])
	subTimer.toc('Installation of plain server')

	subTimer.tic()
	subfixture.create(fixturePath, name='plain', db=db, sql_type=sql_type)
	env.startDatabase(db)
	subTimer.toc('Creation of subfixture plain')

	subTimer.tic()
	env.setupApp(args.install_untested)
	subTimer.toc('Installation of app')

	subTimer.tic()
	subfixture.create(fixturePath, name='main', db=db, sql_type=sql_type)
	subTimer.toc('Creation of subfixture main')

	# env.finishEnvironment()
	fixture.markAsFinished(fixtureConfig)

	l.logger.endGroup()
	timer.toc()

if args.activate_fixture is not None:
	currentFixture.set(args.activate_fixture[0])

if currentFixture.hasCurrent():
	activeFixture = test_runner.dumps.Fixture(currentFixture.getName())
	activeConfig = activeFixture.readConfig()

	if not activeConfig['finished']:
		raise Exception('Cannot use a non-finished fixture. Please rebuild.')
else:
	activeConfig = None

if args.start_helpers or args.run_unit_tests or args.run_integration_tests or args.run_migration_tests:
	if activeConfig is None:
		raise Exception('There is no current fixture configured. Aborting here')

	if not args.quick:
		env.ensureContainersStarted(activeConfig['db'], args.http_server[0])

if args.activate_fixture is not None or args.force_restore:
	timer.tic()
	subfixture.restore(activeFixture.getPath(), activeConfig['db'], activeConfig['sql_type'], 'main', args.quick)
	timer.toc('Restoring state for tests')

returnCode = 0

if args.run_unit_tests or args.run_integration_tests or args.run_migration_tests:
	timer.tic()
	rotation.storeLocation()
	returnCode = runner.runTests(args, subArgs, activeConfig['db'])
	if args.extract_code_coverage and not args.keep_code_coverage:
		rotation.rotateStoredLocation()
	timer.toc('Running all tests')

if args.shutdown_helpers:
	env.stopContainers()

exit(returnCode)

# macro.prepare(args.env_dump_path[0])

# test_runner.docker_management.pullImages()
