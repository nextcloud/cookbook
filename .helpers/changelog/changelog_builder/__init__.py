import logging
import coloredlogs
import subprocess
import datetime
import json

from . import cli
from . import parser
from . import github

_l = logging.getLogger(__name__)
coloredlogs.install(level=5, logger=_l)
logLevelMap = {
	0: logging.WARNING,
	1: logging.INFO,
	2: logging.DEBUG,
}

def main():
	args = cli.parseParams()
	_l.setLevel(logLevelMap.get(args.verbose, 5))
	_l.debug('Found arguments %s', args)

	p = parser.Parser()

	def parseFiles():
		prs = {}
		for filename in args.files:
			prNumber, author, sections = p.parse(filename)
			prs[prNumber] = {
				'author': author,
				'sections': sections
			}
		return prs
	
	prs = parseFiles()
	_l.debug('Found data in files: %s', prs)

	def fixByUpstream(prs):
		with open(args.token) as fp:
			token = fp.read().strip()

		dropPrs = []

		for pr in prs.keys():
			_l.info('Fetching data for PR %d', pr)
			with github.getGitHubPullInformation(pr, token) as res:
				if res.status_code >= 400:
					_l.error('Cannot read pull request information for PR %d', pr)
					raise Exception('No data available')
				
				data = res.json()

			_l.log(5, 'Result %s', json.dumps(data))

			if data['state'] != 'closed':
				if args.pr == str(pr):
					_l.info('The PR %d is the current pull_request to be checked. No commit can be found.', pr)
					prs[pr]['merge_sha'] = 'xxxxxxxx'
				else:
					_l.error('The PR %d is not closed but has a changelog attached. This might be an inconsistency in the code. Skipping it.', pr)
					if args.ci:
						exit(1)
					dropPrs.append(pr)
					continue
			
			if prs[pr]['author'] is None:
				ghAuthor = f'@{data["user"]["login"]}'
				if ghAuthor.endswith('[bot]'):
					ghAuthor = ghAuthor[:-5]
				prs[pr]['author'] = ghAuthor
			
			if args.pr is None or int(args.pr) != pr:
				prs[pr]['merge_sha'] = data['merge_commit_sha']

		for pr in dropPrs:
			prs.pop(pr)
		
		return prs

	prs = fixByUpstream(prs)	

	prsFixed = { k:v for k,v in prs.items() }
	dropProvidedPRId = False
	if args.pr is not None:
		try:
			prsFixed.pop(int(args.pr))
		except KeyError:
			dropProvidedPRId = True

	_l.debug('Fixed data: %s', prs)
	_l.debug('Fixed data (PR filtered out): %s', prsFixed)

	def getSortedPRIds(prs, dropProvidedPRId):
		mergeMap = {}
		for pr in prs:
			mergeMap[prs[pr]['merge_sha']] = pr
		
		_l.debug('Found merge map: %s', mergeMap)

		gitLog = ['git', 'log', '--topo-order', '--pretty=format:%H'] + [prs[pr]['merge_sha'] for pr in prs]
		_l.debug('Command to execute: %s', gitLog)
		proc = subprocess.run(gitLog, text=True, capture_output=True)

		try:
			proc.check_returncode()
		except subprocess.CalledProcessError:
			_l.error('Git log-based sorting failed. Error log:')
			_l.error(proc.stderr)
			raise

		procLines = proc.stdout.split('\n')

		mergeOrder = []
		for procLine in procLines:
			if procLine in mergeMap:
				prId = mergeMap[procLine]
				_l.debug('Found PR %d in topo sorted list', prId)
				mergeOrder.append(prId)
		mergeOrder.reverse()

		if args.pr is not None and not dropProvidedPRId:
			mergeOrder.append(int(args.pr))

		return mergeOrder
	
	mergeOrder = getSortedPRIds(prsFixed, dropProvidedPRId)
	_l.debug('Sorted PRs: %s', mergeOrder)

	def getTotalChangelog():
		sections = {}
		for prId in mergeOrder:
			pr = prs[prId]
			for section, sectionEntries in pr['sections'].items():
				if section not in sections:
					_l.debug('Section %s not found in sections. Creating it.', section)
					sections[section] = []
				
				for entry in sectionEntries:
					sections[section].append(
						(prId, entry, pr['author'])
					)
		return sections
	
	totalSections = getTotalChangelog()
	_l.debug('All merged changelog entries: %s', totalSections)

	def getSectionOrdering():
		knownSections = ('Added', 'Changed', 'Fixed', 'Deprecated', 'Removed', 'Documentation', 'Maintenance')
		# knownSectoinsSet = set(knownSections)
		foundSectionsSet = set(totalSections.keys())
		foundKnownSections = [x for x in knownSections if x in foundSectionsSet]
		foundUnknownSectionsSet = foundSectionsSet.difference(knownSections)
		foundUnknownSections = list(foundUnknownSectionsSet)
		foundUnknownSections.sort()

		if len(foundUnknownSectionsSet) > 0:
			_l.warning('Unknown sections were found: %s', foundUnknownSectionsSet)
			interestingsPRIds = []
			for unknownSection in foundUnknownSectionsSet:
				interestingsPRIds += [entry[0] for entry in totalSections[unknownSection]]
			interestingsPRIds.sort()
			_l.info('Have a look at these PRs: %s', interestingsPRIds)
		
		return foundKnownSections + foundUnknownSections
	
	sectionList = getSectionOrdering()
	
	def getSectionContent():
		lines = []
		if args.tag is not None:
			date = datetime.date.isoformat(datetime.date.today())
			lines.append(f"## {args.tag} - %s" % date)
		
		for section in sectionList:
			lines.append('')
			lines.append(f'### {section}')
			lines.append('')

			for prId, entryLines, author in totalSections[section]:
				firstLine = True
				for l in entryLines:
					if firstLine:
						lines.append(f'- {l}')
						firstLine = False
					else:
						lines.append(f'  {l}')
				lines.append(f'  [#{prId}](https://github.com/nextcloud/cookbook/pull/{prId}) {author}')
		
		lines.append('')
		lines.append('')

		return '\n'.join(lines)

	sectionContent = getSectionContent()
	print(sectionContent)
