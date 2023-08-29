import test_runner.proc as p
import test_runner.docker_helper

class PostgresConnector:
	def __init__(self, user='tester', password='tester_pass', db='nc_test'):
		self.user = user
		self.password = password
		self.db = db
		self.dockerComposeCmd = test_runner.docker_helper.instance.getDockerCompose()

	def getUser(self):
		return self.user

	def getDb(self):
		return self.db

	def getPassword(self):
		return self.password

	def callSQL(self, input, params = [], text=True, capture_output=True, stdin=None):
		cmd = self.dockerComposeCmd + [
			'exec', '-T', 'postgres', 'psql', '-U', self.user, self.db,
			'-v', 'ON_ERROR_STOP=1'
		]
		return p.pr.run(cmd + params, text=text, input=input, capture_output=capture_output, stdin=stdin)

	def dumpDb(self, name):
		cmd = self.dockerComposeCmd + [
			'exec', '-T', 'postgres', 'pg_dump', '-U', self.user, self.db, '--clean', '--if-exists'
		]
		with open(name, 'w') as fp:
			p.pr.run(cmd, stdout=fp).check_returncode()

class MysqlConnector:
	def __init__(self, user='tester', password='tester_pass', db='nc_test'):
		self.user = user
		self.password = password
		self.db = db
		self.dockerComposeCmd = test_runner.docker_helper.instance.getDockerCompose()

	def getUser(self):
		return self.user

	def getDb(self):
		return self.db

	def getPassword(self):
		return self.password

	def callSQL(self, input, params = [], text=True, capture_output=True, stdin=None):
		cmd = self.dockerComposeCmd + [
			'exec', '-T', 'mysql', 'mysql', '-u', self.user,
			'-p{pwd}'.format(pwd=self.password), self.db
		]
		# print('input', input)
		return p.pr.run(cmd + params, text=text, input=input, capture_output=capture_output, stdin=stdin)

	def dumpDb(self, name):
		cmd = self.dockerComposeCmd + [
			'exec', '-T', 'mysql', 'mysqldump', '-u', self.user,
			'-p{pwd}'.format(pwd=self.password), '--add-drop-database', '--database', self.db
		]
		with open(name, 'w') as fp:
			p.pr.run(cmd, stdout=fp).check_returncode()
