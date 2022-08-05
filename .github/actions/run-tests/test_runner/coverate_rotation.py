import test_runner.ci_printer as l

import os
import shutil

class CoverageRotation:
	def __init__(self):
		self.location = None

	def storeLocation(self):
		try:
			self.location = os.readlink('volumes/coverage/latest')
		except FileNotFoundError:
			self.location = None
	
	def rotateStoredLocation(self):
		if self.location is None:
			l.logger.printWarning('No location was stored for rotation')
			return
		
		location = 'volumes/coverage/{name}'.format(name=self.location)

		if os.path.exists(location):
			l.logger.printTask('Removing old coverage from %s' % location)
			shutil.rmtree(location)
			l.logger.printTask('Rotation of code coverage done')
		else:
			l.logger.printNotice('The old code coverage report at %s was not found.' % location)
