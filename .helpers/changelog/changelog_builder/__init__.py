import logging
import coloredlogs
import subprocess
import datetime

from . import cli
from . import parser
from . import github

_l = logging.getLogger(__name__)
coloredlogs.install(level=logging.DEBUG, logger=_l)
logLevelMap = {
	0: logging.WARNING,
	1: logging.INFO,
}

def main():
	args = cli.parseParams()
	_l.setLevel(logLevelMap.get(args.verbose, logging.DEBUG))
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

		for pr in prs.keys():
			_l.debug('Fetching data for PR %d', pr)
			with github.getGitHubPullInformation(pr, token) as res:
				if res.status_code >= 400:
					_l.error('Cannot read pull request information for PR %d', pr)
					raise Exception('No data available')
				
				data = res.json()

			_l.log(5, 'Result %s', data)
			
			if prs[pr]['author'] is None:
				ghAuthor = f'@{data["user"]["login"]}'
				if ghAuthor.endswith('[bot]'):
					ghAuthor = ghAuthor[:-5]
				prs[pr]['author'] = ghAuthor
			
			prs[pr]['merge_sha'] = data['merge_commit_sha']

		return prs

	prs = fixByUpstream(prs)	
	_l.debug('Fixed data: %s', prs)

	def getSortedPRIds():
		mergeMap = {}
		for pr in prs:
			mergeMap[prs[pr]['merge_sha']] = pr
		
		_l.debug('Found merge map: %s', mergeMap)

		gitLog = ['git', 'log', '--topo-order', '--pretty=format:%H'] + [prs[pr]['merge_sha'] for pr in prs]
		_l.debug('Command to execute: %s', gitLog)
		proc = subprocess.run(gitLog, text=True, capture_output=True)
		proc.check_returncode()

		procLines = proc.stdout.split('\n')

		mergeOrder = []
		for procLine in procLines:
			if procLine in mergeMap:
				prId = mergeMap[procLine]
				_l.debug('Found PR %d in topo sorted list', prId)
				mergeOrder.append(prId)
		mergeOrder.reverse()
		return mergeOrder
	
	mergeOrder = getSortedPRIds()
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
