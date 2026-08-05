#!/bin/sh -e

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

if [ $# -lt 1 ]
then
	echo "Please give the path to the old changelog snippets"
	exit 1
fi

localdir="$(dirname "$0")"

versions_dir="$localdir/../../.changelog/versions"
new_version="$1"
shift

. .helpers/changelog/venv/bin/activate

changelog="$localdir/../../CHANGELOG.md"

cat "$localdir/../../.changelog/prefix.md" > "$changelog"

echo "## [Unreleased]" >> "$changelog"
echo "" >> "$changelog"
echo "" >> "$changelog"

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder --tag "$new_version" "$@"
) > ".changelog/versions/v$new_version.md"

rm .changelog/current/*

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat >> "$changelog"

