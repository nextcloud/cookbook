
class FixtureConfig:
	def __init__(self, db='mysql', branch='stable24', phpVersion='8.1', description=None, sql_type='dump'):
		self.db = db
		self.branch = branch
		self.php_version = phpVersion
		self.description = description
		self.sql_type = sql_type

	def parseArgs(self, args):
		self.db = args.input_db[0]
		self.branch = args.create_fixture[1]
		self.php_version = args.php_version[0]
		self.description = args.fixture_description[0] if args.fixture_description is not None else ''
		
		if self.db == 'sqlite':
			self.sql_type = 'none'
		elif args.use_db_dump:
			self.sql_type = 'dump'
		else:
			self.sql_type = 'sync'
