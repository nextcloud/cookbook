#!/bin/bash -e

echo "::notice::Fetching PR information"
curl -L -H "Accept: application/vnd.github+json" "https://api.github.com/repos/nextcloud/cookbook/pulls/$number" > /tmp/pr-data

baseSha=$(cat /tmp/pr-data | jq -r '.base.sha')
echo "::debug::Base SHA is $baseSha"
baseRef=$(cat /tmp/pr-data | jq -r '.base.ref')
echo "::debug::Base reference is $baseRef"
headSha=$(cat /tmp/pr-data | jq -r '.base.sha')
echo "::debug::Head SHA of rebase is $headSha"

IFS=' ' read tmp branchName < /tmp/comment
echo "::debug::Branch name of destination branch is $branchName"

git config user.name "Cookbook bot"
git config user.email "bot@noreply.github.com"

backportBranch() {
	backportBranchName="backport-$branchName/$headSha"
	echo "::debug::Backport branch name to create is $backportBranchName"
	echo "branchName=$backportBranchName" >> "$GITHUB_OUTPUT"
	echo "targetBranch=$baseRef" >> "$GITHUB_OUTPUT"
	
	git branch "$backportBranchName" "$headSha"
	
	echo "::debug::Switching to backport branch"
	git checkout "$backportBranchName"

	echo "Branch was created, ready for porting to other branch"

	git rebase --onto "$branchName" "$baseSha" "$backportBranchName"

	echo "Rebasing was successful"

	git push origin "$backportBranchName"
}

backportBranch
