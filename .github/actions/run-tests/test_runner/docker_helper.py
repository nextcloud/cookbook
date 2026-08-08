# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

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
