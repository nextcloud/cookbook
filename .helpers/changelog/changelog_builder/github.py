import requests

def getGitHubPullInformation(pr: int, token: str):
	url = f'https://api.github.com/repos/nextcloud/cookbook/pulls/{pr}'
	headers= {
		'Authorization': f'Bearer {token}'
	}
	res = requests.get(url, headers=headers)
	return res
