
class CILogger:
	def __init__(self, isCi):
		self.ci = isCi

	def startGroup(self, name):
		if self.ci:
			print('::group::{name}'.format(name=name))
	def endGroup(self):
		if self.ci:
			print('::end group::')
	
	def printTask(self, line):
		print('[T] {line}'.format(line=line))
	
	def printToc(self, line):
		print(line)
	
	def printDebug(self, line):
		if self.ci:
			print('::debug::{line}'.format(line=line))
		else:
			print('[D] {line}'.format(line=line))
	
	def printWithFile(self, line, cmd, file=None, title=None, lineStart=None, lineEnd=None,colStart=None, colEnd=None):
		if self.ci:
			options = {}
			
			if file is not None:
				options['file'] = file
			if title is not None:
				options['title'] = title
			if lineStart is not None:
				options['line'] = lineStart
			if colStart is not None:
				options['col'] = colStart
			if lineEnd is not None:
				options['endLine'] = lineEnd
			if colEnd is not None:
				options['endColumn'] = colEnd
			
			if len(options.keys()) > 0:
				location = ' {csv}'.format(csv=",".join(['{k}={v}'.format(k=k, v=options[k]) for k in options.keys()]))
			else:
				location = ''
			
			print("::{cmd}{loc}::{line}".format(cmd=cmd, loc=location, line=line))
		else:
			mapping = {
				'warning': 'W',
				'error': 'E',
				'notice': 'N',
			}
			print('[{sym}] {line}'.format(sym=mapping[cmd], line=line))
		
	def printNotice(self, line, *args, **kwargs):
		self.printWithFile(line, 'notice', *args, **kwargs)
	def printWarning(self, line, *args, **kwargs):
		self.printWithFile(line, 'warning', *args, **kwargs)
	def printError(self, line, *args, **kwargs):
		self.printWithFile(line, 'error', *args, **kwargs)
	
	def printSetOutput(self, name, value):
		if self.ci:
			print('::set-output name={name}::{value}'.format(name=name, value=value))
		else:
			print('[O:{name}] {value}'.format(name=name, value=value))

logger = CILogger(False)
