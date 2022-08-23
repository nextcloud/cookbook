import test_runner.ci_printer as l
import test_runner.db
import test_runner.test_env
import test_runner.proc as p

import json
import os
import shutil
import re

try:
	import tabulate
	useTabulate = True
except ImportError:
	useTabulate = False

class SubFixture:
	
	rsyncArgs = ['--delete', '--delete-delay', '--delete-excluded', '--archive']

	def __init__(self):
		pass
	
	def __cloneFiles(self, subFixturePath):
		l.logger.printTask('Save the data files')
		p.pr.run(
			['rsync', 'volumes/data/', '{path}/data'.format(path=subFixturePath)] + SubFixture.rsyncArgs
		).check_returncode()

		l.logger.printTask('Save the server files')
		p.pr.run(
			['rsync', 'volumes/nextcloud/', '{path}/nextcloud'.format(path=subFixturePath)] + SubFixture.rsyncArgs
		).check_returncode()
		l.logger.printTask('Saving of files has been done')

	def __restoreFiles(self, subFixturePath, quickMode = False):
		l.logger.printTask('Restore the data files')
		p.pr.run(
			['rsync', '{path}/data/'.format(path=subFixturePath), 'volumes/data/'] + SubFixture.rsyncArgs
		).check_returncode()

		if quickMode:
			l.logger.printNotice('Server files will not be restored in quick mode')
		else:
			l.logger.printTask('Restore the server files')
			p.pr.run(
				['rsync', '{path}/nextcloud/'.format(path=subFixturePath), 'volumes/nextcloud/'] + SubFixture.rsyncArgs
			).check_returncode()
		
		l.logger.printTask('Restoring of files has been done')

	def __createSQLDump(self, path, db):
		sqlFileName = '{path}/dump.sql'.format(path=path)

		def handleMySql():
			caller = test_runner.db.MysqlConnector()
			caller.dumpDb(sqlFileName)

		def handlePostgres():
			caller = test_runner.db.PostgresConnector()
			caller.dumpDb(sqlFileName)

		def handleSqlite():
			l.logger.printDebug('Sqlite does not support sql dumps.')

		mapping = {
			'mysql': handleMySql,
			'pgsql': handlePostgres,
			'sqlite': handleSqlite,
		}
		mapping[db]()
	
	def __restoreSQLDump(self, path, db):
		sqlFileName = '{path}/dump.sql'.format(path=path)

		def handleMySql():
			caller = test_runner.db.MysqlConnector()
			with open(sqlFileName, 'r') as fp:
				caller.callSQL(None, stdin=fp,capture_output=False).check_returncode()

		def handlePostgres():
			caller = test_runner.db.PostgresConnector()
			with open(sqlFileName, 'r') as fp:
				caller.callSQL(None, stdin=fp, capture_output=False).check_returncode()

		def handleSqlite():
			l.logger.printDebug('Sqlite does not support sql dumps.')

		mapping = {
			'mysql': handleMySql,
			'pgsql': handlePostgres,
			'sqlite': handleSqlite,
		}
		mapping[db]()

	def __syncDbData(self, path, db):
		def saveData(folder):
			p.pr.run(['sudo', 'rsync', folder, path] + self.rsyncArgs).check_returncode()

		def handleMySql():
			saveData('volumes/mysql/')

		def handlePostgres():
			saveData('volumes/postgres/')

		def handleSqlite():
			l.logger.printDebug('Sqlite data is stored with main data. Thus is does not need manual syncing.')

		mapping = {
			'mysql': handleMySql,
			'pgsql': handlePostgres,
			'sqlite': handleSqlite,
		}
		mapping[db]()

	def __restoreSyncedDbData(self, path, db):
		def restoreData(folder):
			p.pr.run(['sudo', 'rsync', path, folder] + self.rsyncArgs).check_returncode()

		def handleMySql():
			restoreData('volumes/mysql/')

		def handlePostgres():
			restoreData('volumes/postgres/')

		def handleSqlite():
			l.logger.printDebug('Sqlite data is stored with main data. Thus is does not need manual syncing.')

		mapping = {
			'mysql': handleMySql,
			'pgsql': handlePostgres,
			'sqlite': handleSqlite,
		}
		mapping[db]()

	def __shutdownDb(self, db):
		env = test_runner.test_env.Environment()
		env.stopDatabase(db)

	def __startDb(self, db):
		env = test_runner.test_env.Environment()
		env.startDatabase(db)

	def __createDbDump(self, subFixturePath, db, sql_type):
		
		sqlPath = '{path}/sql'.format(path=subFixturePath)

		def handleDump():
			self.__createSQLDump(sqlPath, db)

		def handleSync():
			self.__shutdownDb(db)
			self.__syncDbData(sqlPath, db)

		def handleNone():
			l.logger.printDebug('Skipping DB handling as sql_type is set to none.')

		mapping = {
			'dump': handleDump,
			'sync': handleSync,
			'none': handleNone,
		}

		l.logger.printTask('Creating clone of the database')
		mapping[sql_type]()
		l.logger.printTask('Database clone was created')
	
	def __restoreDbDump(self, subFixturePath, db, sql_type):
		
		sqlPath = '{path}/sql'.format(path=subFixturePath)

		def handleDump():
			self.__restoreSQLDump(sqlPath, db)

		def handleSync():
			self.__shutdownDb(db)
			self.__restoreSyncedDbData('%s/' % sqlPath, db)
			self.__startDb(db)

		def handleNone():
			l.logger.printDebug('Skipping DB handling as sql_type is set to none.')

		mapping = {
			'dump': handleDump,
			'sync': handleSync,
			'none': handleNone,
		}

		l.logger.printTask('Restoring clone of the database')
		mapping[sql_type]()
		l.logger.printTask('Database was restored')

	def create(self, fixturePath, name='main', db='mysql', sql_type='dump'):
		subFixturePath = '{fixture}/{name}'.format(fixture=fixturePath, name=name)
		l.logger.printDebug('Creating sub-fixture in {path}'.format(path=subFixturePath))

		self.__cloneFiles(subFixturePath)
		self.__createDbDump(subFixturePath, db, sql_type)

	def restore(self, fixturePath, db, sql_type, name='main', quickMode=False):
		subFixturePath = '{fixture}/{name}'.format(fixture=fixturePath, name=name)
		l.logger.printDebug('Restoring from sub-fixture in {path}'.format(path=subFixturePath))

		self.__restoreFiles(subFixturePath, quickMode)
		self.__restoreDbDump(subFixturePath, db, sql_type)

class Fixture:
	version = 1

	def __init__(self, name):
		self.name = name
		self.path = 'volumes/dumps/fixtures/{name}'.format(name=name)
	
	def readConfig(self):
		configFile = '{path}/config.json'.format(path=self.path)

		if not os.path.exists(configFile):
			raise Exception('No config file was found for fixture {fix}'.format(fix=self.name))
		
		with open(configFile, 'r') as fp:
			config = json.load(fp)
		
		return config

	def __writeConfigFile(self, fixtureConfig, finished=False, version=1):
		config = {
			'finished': finished,
			'db': fixtureConfig.db,
			'branch': fixtureConfig.branch,
			'php_version': fixtureConfig.php_version,
			'description': fixtureConfig.description,
			'version': version,
			'sql_type': fixtureConfig.sql_type
		}
		configFile = '{path}/config.json'.format(path=self.path)
		with open(configFile, 'w') as fp:
			json.dump(config, fp)

	def prepareFolders(self, fixtureConfig, overwrite_fixture):
		if os.path.exists(self.path):
			if not overwrite_fixture:
				raise Exception('Cannot overwrite existing fixture')

			if not os.path.isdir(self.path):
				raise Exception('The given name of the fixture is no folder. Aborting.')
			
			if os.path.exists('{path}/config.json'.format(path=self.path)):
				config = self.readConfig()
				version = config.get('version', 0)
				
				if version > Fixture.version:
					raise Exception('Cannot do a back-migration of the fixture. Please update source code.')
				elif version < Fixture.version:
					raise Exception('Cannot migrate previous version of fixture to current one yet.')
				else:
					self.drop()
					os.mkdir(self.path)
			else:
				raise Exception('Migration of dump without config is not supported yet.')
			
		else:
			os.mkdir(self.path)
		# Main fixture path is present now
		
		# Create the required paths in the folder
		for p in ('main', 'plain'):
			pName = '{path}/{name}'.format(path=self.path, name=p)
			if not os.path.isdir(pName):
				os.mkdir(pName)
			
			for p1 in ('sql', 'data', 'nextcloud'):
				p1Name = '{path}/{name}'.format(path=pName, name=p1)
				if not os.path.isdir(p1Name):
					os.mkdir(p1Name)
		
		# Initialize the config file
		self.__writeConfigFile(fixtureConfig, False)

		return self.path

	def markAsFinished(self, fixtureConfig):
		self.__writeConfigFile(fixtureConfig, True)
	
	def getPath(self):
		return self.path

	def drop(self):
		p.pr.run(['sudo', 'rm', '-rf', self.path]).check_returncode()

class CurrentFixture:
	def __init__(self):
		self.path = 'volumes/dumps/current'
	
	def validate(self):
		if os.path.islink(self.path) and not os.path.exists(self.path):
			# Broken symlink
			os.remove(self.path)
		
	def set(self, name):
		fixture = Fixture(name)
		fixture.readConfig()

		if os.path.islink(self.path):
			os.remove(self.path)
		elif os.path.isdir(self.path):
			p.pr.run(['sudo', 'rm', '-rf', self.path]).check_returncode()
		elif os.path.exists(self.path):
			raise Exception('Unknown typo of object for current fixture')
		
		os.symlink('fixtures/{name}'.format(name=name), self.path, target_is_directory=True)

	def hasCurrent(self):
		return os.path.exists(self.path)
	
	def getName(self):
		if not os.path.islink(self.path):
			return None
		pattern = 'fixtures/([^/]+)'
		target = os.readlink(self.path)
		match = re.fullmatch(pattern, target)
		if match is None:
			raise Exception('Cannot parse name of current fixture. Please fix manually')
		return match.group(1)
		
class DumpsManager:
	def __init__(self):
		self.path = 'volumes/dumps'

	def validate(self):
		configFileName = '{path}/version.json'.format(path=self.path)
		if not os.path.exists(configFileName):
			entries = os.listdir(self.path)
			if len(entries) > 0:
				raise Exception('There is no version configuration present.')

	def init(self):
		self.validate()

		configFileName = '{path}/version.json'.format(path=self.path)
		if not os.path.exists(configFileName):
			with open(configFileName, 'w') as fp:
				config = {
					'version': 1
				}
				json.dump(config, fp)
		
		fixturesPath = '{path}/fixtures'.format(path=self.path)
		if not os.path.isdir(fixturesPath):
			os.mkdir(fixturesPath)

	def getFixtures(self):
		self.validate()

		entries = os.listdir('{path}/fixtures'.format(path=self.path))

		return entries
	
	def listFixtures(self):
		if useTabulate:
			fixtures = self.getFixtures()
			fixtureInfos = [{'config': Fixture(f).readConfig(), 'name': f} for f in fixtures]

			cf = CurrentFixture()
			current = cf.getName()
			for f in fixtureInfos:
				f['active'] = 'X' if f['name'] == current else ''

			table = []
			for x in fixtureInfos:
				if not x['config']['finished']:
					continue

				db = x['config']['db']
				if db in ('mysql', 'pgsql'):
					db = '{db} ({type})'.format(db=db, type=x['config']['sql_type'][0])
				
				entry = [
					x['name'], x['active'], x['config']['description'], x['config']['branch'], db, x['config']['php_version']
				]
				table.append(entry)
			print(tabulate.tabulate(table, headers=['name', 'current', 'description', 'NC branch', 'DB', 'PHP']))
		else:
			l.logger.printError('tabulate could not be loaded. Please make sure the package is installed and a venv is activated if required.')
			exit(1)
