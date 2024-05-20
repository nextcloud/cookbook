import json
import toml
import logging
import re
import os

class ParserInvalidException(Exception):
	def __init__(self, *args):
		super().__init__(*args)

class MarkdownParser:
	
	STATE_INIT = 1
	STATE_SECTION_STARTED = 2
	STATE_SECTION_RUNNING = 3

	def __init__(self):
		self._l = logging.getLogger(__name__)
		self._reAuthors = re.compile('Authors?:\W+(.*)')
		self._reHeader = re.compile('#+\W+(.*)')

		self._state = None

		self._author = None
		self._header = None
		self._lines = None
		self._sections = None
	
	def parse(self, filename: str):
		self._l.debug('Parsing %s with MarkdownParser', filename)

		if not filename.endswith('.md'):
			raise ParserInvalidException('No markdown file')
		
		with open(filename) as fp:
			self._state = self.STATE_INIT
			self._author = None
			self._sections = {}

			stateMap = {
				self.STATE_INIT: lambda line: self._parseInit(line),
				self.STATE_SECTION_STARTED: lambda line: self._parseStartedSection(line),
				self.STATE_SECTION_RUNNING: lambda line: self._parseRunningSection(line),
			}

			for line in fp.readlines():
				strippedLine = line.rstrip()
				if strippedLine == '':
					continue

				self._l.debug('Parsing line "%s"', strippedLine)
				stateMap[self._state](strippedLine)
			self._pushLinesToStruct()
		
		self._l.debug('Found datastructure: %s', self._sections)
		return self._author, self._sections
	
	def _parseInit(self, line: str):
		matcherAuthor = self._reAuthors.fullmatch(line)
		if matcherAuthor is not None:
			self._author = matcherAuthor.group(1)
			return
		
		if not self._headerMatched(line):
			self._l.error('No heading line is defined')
			raise ParserInvalidException()

	def _headerMatched(self, line: str):
		matcherHeader = self._reHeader.fullmatch(line)
		if matcherHeader is None:
			return False
		
		self._pushLinesToStruct()

		self._header = matcherHeader.group(1)
		self._state = self.STATE_SECTION_STARTED
		self._lines = []
		self._l.debug('Started section %s', self._header)

		return True
	
	def _parseStartedSection(self, line: str):
		if self._headerMatched(line):
			# There is nothing in this section. We do not need to finish anything
			return
		
		lineSnippet = self._getListSnippet(line)
		if lineSnippet is None:
			self._l.error('Starting section %s with a non-list element', self._header)
			raise ParserInvalidException()
		
		self._l.debug('Adding line snippet "%s"', lineSnippet)
		self._lines.append(lineSnippet)
		self._state = self.STATE_SECTION_RUNNING
	
	def _getListSnippet(self, line):
		if not ( line.startswith('-') or line.startswith('*') ):
			return None
		
		lineSnippet = line[1:].strip()
		return lineSnippet

	def _parseRunningSection(self, line: str):
		if self._headerMatched(line):
			return

		listSnippet = self._getListSnippet(line)
		if listSnippet is None:
			self._l.debug('Appending line as no new snippet')
			self._lines.append(line.strip())
		else:
			self._l.debug('New list snippet found.')
			self._pushLinesToStruct()

			self._lines = [listSnippet]

	def _pushLinesToStruct(self):
		if self._lines is None:
			return
		
		if len(self._lines) == 0:
			self._l.debug('Not pushing lines to the struct as no lines are present')
			return

		self._l.debug('Pushing lines to the structure')
		sectionList = self._sections.get(self._header, [])
		sectionList.append(self._lines)
		self._sections[self._header] = sectionList
		self._lines = []

class Parser:
	def __init__(self):
		self._l = logging.getLogger(__name__)
		self._parsers = (
			MarkdownParser(),
		)
		self._rePrNumber = re.compile('([0-9]+)-.*')
	
	def parse(self, path: str):
		self._l.info('Parsing file %s', path)

		filename = os.path.basename(path)
		matcher = self._rePrNumber.fullmatch(filename)
		if matcher is None:
			self._l.error('The filename %s does not contain a valid  PR number at the beginning.', filename)
			raise ParserInvalidException('Cannot extract PR number from filename')

		prNumber = int(matcher.group(1))

		for p in self._parsers:
			try:
				author, sections = p.parse(path)
				return prNumber, author, sections
			except ParserInvalidException:
				self._l.debug('Parser rejected file.')
		
		raise ParserInvalidException('No parser was found')
