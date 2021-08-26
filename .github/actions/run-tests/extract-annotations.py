#!/bin/env python

import argparse
import os.path
import re

parser = argparse.ArgumentParser()

parser.add_argument('file', nargs=1, help='The log file to parse')
args = parser.parse_args()

logfile = args.file[0]
if not os.path.isfile(logfile):
	print(f"The file {logfile} could not be found.")
	exit(1)

detailsMatcher = re.compile("name='([^']*)' message='([^']*)'.*?details='\s*([^:]*):([0-9]+).*'")
fileNameMatcher = re.compile("/nextcloud/custom_apps/cookbook/(.*)")
def parseTestDetails(l):
	match = detailsMatcher.search(l)
	ret = {}
	ret['name'] = match.group(1)
	ret['message'] = match.group(2)
	ret['line'] = match.group(4)
	
	match2 = fileNameMatcher.match(match.group(3))
	ret['file'] = match2.group(1)
	
	return ret

def parseIgnoredLine(l):
	details = parseTestDetails(l)
	#print(l)
	#print(details)
	
	if details['message'] == '':
		message = f"The test {details['name']} is skipped."
	else:
		message = details['message']
	
	print(f"::warning file={details['file']},line={details['line']}::{message}")

def parseFailedLine(l):
	details = parseTestDetails(l)
	#print(l)
	#print(details)
	
	print(f"::error file={details['file']},line={details['line']}::{details['message']}")

matchers = [
	{
		'regex': '^##teamcity\\[testIgnored',
		'fcn': parseIgnoredLine
	},
	{
		'regex': '^##teamcity\\[testFailed',
		'fcn': parseFailedLine
	}
	]

for m in matchers:
	m['m'] = re.compile(m['regex'])


def parseLine(l):
	for m in matchers:
		if m['m'].match(l):
			m['fcn'](l)
			return
	
with open(logfile, 'r') as f:
	while True:
		l = f.readline()
		
		if not l:
			break
		
		parseLine(l)
		
		#print(l)

#print(matchers)

exit(0)
