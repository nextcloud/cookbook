# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import requests

def getGitHubPullInformation(pr: int, token: str):
	url = f'https://api.github.com/repos/nextcloud/cookbook/pulls/{pr}'
	headers= {
		'Authorization': f'Bearer {token}'
	}
	res = requests.get(url, headers=headers)
	return res
