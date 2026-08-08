#!/bin/bash

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

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

echo "Base Reference: $BASE_REF"
echo "Head Reference: $BRANCH_REF"

files="$(git diff --name-only "$BASE_REF...$BRANCH_REF")"

echo '::group::Updated files'
echo "$files"
echo '::endgroup::'

echo "$files" | grep -E '[.](php|phpt|vue|js)$' | while read line
do
	file=$(echo "$line" | sed 's@^\./@@')

	grep -noE '(TODO|ToDo|@todo|XXX|FIXME|FixMe)([^a-zA-Z0-9].*)?$' "$line" | while read match
	do
		IFS=: read lineno msg <<< "$match"
		echo "::warning file=$file,line=$lineno::Found $msg"
	done
done
