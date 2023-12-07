#!/bin/bash -e

if [ "$DEBUG" = '1' ]
then
	set -x
fi

echo "::notice::Fetching PR information"
curl -L -H "Accept: application/vnd.github+json" "https://api.github.com/repos/nextcloud/cookbook/pulls/$number" > /tmp/pr-data

baseSha=$(cat /tmp/pr-data | jq -r '.base.sha')
echo "::debug::Base SHA is $baseSha"
baseRef=$(cat /tmp/pr-data | jq -r '.base.ref')
echo "::debug::Base reference is $baseRef"
headSha=$(cat /tmp/pr-data | jq -r '.head.sha')
echo "::debug::Head SHA of rebase is $headSha"
headRef=$(cat /tmp/pr-data | jq -r '.head.ref')
echo "::debug::Reference of rebase is $headRef"

IFS=' ' read tmp branchName < /tmp/comment
branchName="$(echo "$branchName" | tr -cd "a-zA-Z0-9+=_/,.-")"
echo "::debug::Branch name of destination branch is $branchName"

git config user.name "Cookbook bot"
git config user.email "bot@noreply.github.com"

backportBranch() {
	backportBranchName="backport-$branchName-$number/$headRef"
	echo "::debug::Backport branch name to create is $backportBranchName"
	echo "branchName=$backportBranchName" >> "$GITHUB_OUTPUT"
	echo "targetBranch=$branchName" >> "$GITHUB_OUTPUT"
	
	git branch "$backportBranchName" "$headSha"
	
	echo "::debug::Switching to backport branch"
	git checkout "$backportBranchName"

	echo "Branch was created, ready for porting to other branch"

	git rebase --onto "origin/$branchName" "$baseSha" "$backportBranchName"

	echo "Rebasing was successful"

	git checkout "$branchName"
	git merge --ff-only "$backportBranchName"
	git branch -d "$backportBranchName"
}

backportBranch
