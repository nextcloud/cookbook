#!/bin/sh

if [ $# -lt 2 ]
then
	echo "Please give the path to the old changelog snippets"
	exit 1
fi

versions_dir="$1"
new_version="$2"
shift 2

. .helpers/changelog/venv/bin/activate

echo "## [Unreleased]"

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder "$@"
) > ".changelog/versions/v$new_version.md"

rm .changelog/current/*

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat

