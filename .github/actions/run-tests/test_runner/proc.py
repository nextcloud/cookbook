
import subprocess
import sys
import os

class Proc:
	def __init__(self):
		self.defaultEnv = os.environ
		self.defaultEnv['COMPOSE_COMPATIBILITY'] = 'true'
		self.defaultEnv['RUNNER_UID'] = str(os.getuid())
		self.defaultEnv['RUNNER_GID'] = str(os.getgid())

	def run(self, *args, text=True, env=None, stderr=subprocess.STDOUT, **kwargs):
		sys.stdout.flush()
		sys.stderr.flush()

		usedEnv = env if env is not None else self.defaultEnv
	
		return subprocess.run(*args, text=text, env=usedEnv, **kwargs)

pr = Proc()
