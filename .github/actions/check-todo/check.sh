#!/bin/bash

# set -x

BRANCH_REF=HEAD
BASE_REF=master

if [ $# -gt 0 ]; then
	BRANCH_REF="$1"
	shift
fi

if [ $# -gt 0 ]; then
	BASE_REF="$1"
	shift
fi

git diff --name-only "$BASE_REF...$BRANCH_REF" | grep -E '[.](php|phpt|vue|js)$' | while read line
do
	file=$(echo "$line" | sed 's@^\./@@')

	grep -noE '(TODO|ToDo|@todo|XXX|FIXME|FixMe)([^a-zA-Z].*)?$' "$line" | while read match
	do
		IFS=: read lineno msg <<< "$match"
		echo "::warning file=$file,line=$lineno::Found $msg"
	done
done
