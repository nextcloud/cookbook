class DockerHelper:
	def __init__(self, args):
		self.useLegacy = args.use_docker_compose_legacy
	
	def getDockerCompose(self):
		if self.useLegacy:
			return ['docker-compose']
		else:
			return ['docker', 'compose']

instance = None

def initialize(args):
	global instance
	instance = DockerHelper(args)
