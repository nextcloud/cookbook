import argparse

def parseParams():
	parser = argparse.ArgumentParser()

	parser.add_argument('files', nargs='+')
	parser.add_argument('-t', '--token', required=True, help='The file containing the token to use when making API requests.')
	parser.add_argument('--tag', help='The version to tag this with')
	parser.add_argument('--ci', action='store_true', help='Make the script more restrictive for use in CI.')
	
	parser.add_argument('-v', '--verbose', default=0, action='count')
	return parser.parse_args()
