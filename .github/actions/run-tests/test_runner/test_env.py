import test_runner.ci_printer as l
import test_runner.db
import test_runner.timer
import test_runner.proc as p

import time
import os

class Environment:
	def __init__(self):
		pass

	def ensureFolderStructureExists(self):
		if not os.path.isdir('volumes'):
			os.mkdir('volumes')
		
		volumeFolders = (
			'nextcloud', 'data', 'cookbook', 'mysql', 'postgres', 'www',
			'dumps', 'coverage', 'output'
		)
		for f in volumeFolders:
			p = os.path.join('volumes', f)
			if os.path.exists(p) and not os.path.isdir(p):
				raise Exception('Cannot create folder structure appropriately. Offending folder is %s' % p)
			
			if not os.path.exists(p):
				os.mkdir(os.path.join('volumes', f))
	
	def startDatabase(self, db):
		def startMysql():
			def checkRunning():
				cmd = ['docker', 'inspect', '--format', '{{.State.Health.Status}}', 'cookbook_unittesting_mysql_1']
				for i in range(0,20):
					sp = p.pr.run(cmd, capture_output=True, text=True)
					sp.check_returncode()
					if sp.stdout.strip() == 'healthy':
						return
					time.sleep(1)
				
				raise Exception('Starting of MySQL container failed.')

			l.logger.printDebug('Starting MySQL database container')
			p.pr.run(['docker-compose', 'up', '-d', 'mysql']).check_returncode()
			checkRunning()
		
		def startPostgresql():
			def checkRunning(pgCaller):
				for i in range(0,20):
					ret = pgCaller.callSQL('\\q')
					if ret.returncode == 0:
						return
					time.sleep(1)
				raise Exception('Starting of PostgreSQL failed.')
			
			l.logger.printDebug('Starting PostgreSQL container')
			p.pr.run(['docker-compose', 'up', '-d', 'postgres']).check_returncode()
			
			pgCaller = test_runner.db.PostgresConnector()
			checkRunning(pgCaller)

		def startSqlite():
			l.logger.printDebug('No need to setup SQLite database')

		mappingDb = {
			'mysql': startMysql,
			'pgsql': startPostgresql,
			'sqlite': startSqlite,
		}

		l.logger.printTask('Starting the helper containers')
		mappingDb[db]()
		l.logger.printTask('Database was started successfully')

	def stopDatabase(self, db):
		def stopContainer(name):
			l.logger.printTask('Stopping container {name}'.format(name=name))
			p.pr.run(['docker-compose', 'stop', name]).check_returncode()

		def handleMySql():
			stopContainer('mysql')

		def handlePostgres():
			stopContainer('postgres')

		def handleSqlite():
			l.logger.printDebug('Sqlite does not support shutting down the service container.')

		mapping = {
			'mysql': handleMySql,
			'pgsql': handlePostgres,
			'sqlite': handleSqlite,
		}
		mapping[db]()
	
	def stopContainers(self):
		l.logger.printTask('Stopping remaining containers')
		p.pr.run(['docker-compose', 'stop']).check_returncode()

	def __dropEnvironment(self, db):
		def cleanMysql():
			caller = test_runner.db.MysqlConnector()
			sp = caller.callSQL('SHOW TABLES;')
			# print(sp)
			tables = sp.stdout.split('\n')[1:]
			if len(tables) > 0:
				sqls = ['DROP TABLE {t};'.format(t=t) for t in tables if t != '']
				sql = "\n".join(sqls)
				caller.callSQL(sql, capture_output=True).check_returncode()
		
		def cleanPostgres():
			caller = test_runner.db.PostgresConnector()
			sql = '''DROP SCHEMA public CASCADE;
			CREATE SCHEMA public;
			GRANT ALL ON SCHEMA public TO {user};
			GRANT ALL ON SCHEMA public TO PUBLIC;
			ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO PUBLIC;'''.format(user=caller.getUser())
			caller.callSQL(sql, capture_output=True).check_returncode()
			
		def cleanSqlite():
			pass
		
		l.logger.printTask('Dropping the environment')

		mappingDb = {
			'mysql': cleanMysql,
			'pgsql': cleanPostgres,
			'sqlite': cleanSqlite,
		}

		l.logger.printTask('Cleaning the database')
		mappingDb[db]()
		l.logger.printTask('Database has been reset.')
		
		l.logger.printTask('Cleaning out the NC files.')
		p.pr.run('rm -rf volumes/{data,nextcloud}/{*,.??*}', shell=True).check_returncode()
		l.logger.printTask('Removal of files done.')

		l.logger.printTask('Dropping of the environment done.')


	def __runPhpScript(self, filename):
		with open(filename, 'r') as fp:
			data = fp.read()
		
		return p.pr.run(
			['docker-compose', 'run', '--rm', '-T', 'php'], input=data,
			capture_output=True, text=True
		)
	
	def __setupServer(self, db, branch):
		l.logger.printTask('Starting installing the server')

		timer = test_runner.timer.Timer()

		l.logger.printTask('Checkout of the Nextcloud core using git.')
		timer.tic()
		p.pr.run([
			'git', 'clone', '--depth=1', '--branch', branch,
			'https://github.com/nextcloud/server', 'volumes/nextcloud'
		]).check_returncode()

		l.logger.printTask('Checkout of the submodules')
		p.pr.run([
			'git', 'submodule', 'update', '--init', '--depth', '1'
		], cwd='volumes/nextcloud').check_returncode()
		timer.toc('Checkout of git repos')
		
		l.logger.printTask('Create folder structure for correct permissions')
		p.pr.run(['mkdir', '-p', 'custom_apps/cookbook', 'data'], cwd='volumes/nextcloud').check_returncode()

		def installGeneric(options):
			cmd = [
				'docker-compose', 'run', '--rm', '-T', 'occ', 'maintenance:install',
				'--admin-user', 'admin', '--admin-pass', 'admin'
			] + options

			p.pr.run(cmd).check_returncode()
		
		def installWithMysql():
			caller = test_runner.db.MysqlConnector()
			installGeneric([
				'--database', 'mysql', '--database-host', 'mysql', '--database-name', caller.getDb(),
				'--database-user', caller.getUser(), '--database-pass', caller.getPassword()
			])
		def installWithPgSQL():
			caller = test_runner.db.PostgresConnector()
			installGeneric([
				'--database', 'pgsql', '--database-host', 'postgres', '--database-name', caller.getDb(),
				'--database-user', caller.getUser(), '--database-pass', caller.getPassword()
			])
		def installWithSQLite():
			installGeneric(['--database', 'sqlite'])
		
		mappingDb = {
			'mysql': installWithMysql,
			'pgsql': installWithPgSQL,
			'sqlite': installWithSQLite,
		}

		l.logger.printTask('Installing NC server instance')
		timer.tic()
		mappingDb[db]()
		timer.toc('Server installation')

		l.logger.printTask('Running auxiliary post-install scripts')
		self.__runPhpScript('scripts/set_debug_mode.php')
		self.__runPhpScript('scripts/set_custom_apps_path.php')

		l.logger.printTask('Installation of NC server is finished.')

	def setupApp(self, installUntested):
		timer = test_runner.timer.Timer()

		l.logger.startGroup('Installation of the cookbook app')
		timer.tic()
		l.logger.printTask('Installing cookbook app')

		if installUntested:
			l.logger.printTask('Adding exception for app to be allowed as untested app')
			self.__runPhpScript('scripts/enable_app_install_script.php')
		
		l.logger.printTask('Synchronize the app code base to volume')
		excludes = ['/.git/', '/.github/actions/run-tests/volumes/', '/node_modules/']
		excludePairs = [['--exclude', x] for x in excludes]
		excludeParams = [x for pair in excludePairs for x in pair]
		p.pr.run(
			['rsync', '-a', '../../../', 'volumes/cookbook', '--delete', '--delete-delay'] + excludeParams
		).check_returncode()

		l.logger.printTask('Making appinfo file')
		p.pr.run(['make', 'appinfo/info.xml'], cwd='volumes/cookbook').check_returncode()

		l.logger.printTask('Activate the cookbook app in the server')
		p.pr.run(
			['docker-compose', 'run', '--rm', '-T', 'occ', 'app:enable', 'cookbook']
		).check_returncode()

		l.logger.printTask('Installation of cookbook finished')
		l.logger.endGroup()
		timer.toc('Installation of cookbook app')

	def prepareBasicEnvironment(self, db, branch):
		timer = test_runner.timer.Timer()
		totalTimer = test_runner.timer.Timer()

		l.logger.startGroup('Preparing server environment')
		totalTimer.tic()
		
		timer.tic()
		self.startDatabase(db)
		timer.toc('Started database')
		
		timer.tic()
		self.__dropEnvironment(db)
		timer.toc('Environment cleaned')
		
		timer.tic()
		self.__setupServer(db, branch)
		timer.toc('Server installed')
		
		totalTimer.toc('Environment preparation')
		l.logger.endGroup()

	def __finishEnvironment(self, http_server):
		timer = test_runner.timer.Timer()
		l.logger.startGroup('Start containers (post-install)')
		timer.tic()

		l.logger.printTask('Start fpm and www containers')
		p.pr.run(['docker-compose', 'up', '-d', 'www', 'fpm']).check_returncode()

		httpMapper = {
			'apache': 'apache',
			'nginx': 'nginx',
		}
		l.logger.printTask('Start HTTP server')
		p.pr.run(
			['docker-compose', 'up', '-d', httpMapper[http_server]]
		).check_returncode()

		l.logger.printTask('Started all containers')
		l.logger.endGroup()

	def ensureContainersStarted(self, db, http_server):
		self.startDatabase(db)
		self.__finishEnvironment(http_server)
