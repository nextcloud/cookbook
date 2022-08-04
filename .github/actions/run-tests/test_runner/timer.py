import time
import test_runner.ci_printer

class Timer:
	def __init__(self):
		pass

	def tic(self):
		self.time = time.monotonic()
	
	def toc(self, line = None):
		ts = time.monotonic()
		diff = ts - self.time

		if line is None:
			text = 'Elapsed time: {time}'.format(time=diff)
		else:
			text = 'Elapsed time ({line}): {time}'.format(line=line, time=diff)
		
		test_runner.ci_printer.logger.printToc(text)
