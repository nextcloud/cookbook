#!/bin/sh

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

echo "## [Unreleased]"
echo ""
echo ""

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder --tag "$new_version" "$@"
) > ".changelog/versions/v$new_version.md"

rm .changelog/current/*

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat > "$localdir/../../CHANGELOG.md"

