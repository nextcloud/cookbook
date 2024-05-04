#!/bin/sh

if [ $# -lt 1 ]
then
	echo "Please give the path to the old changelog snippets"
	exit 1
fi

versions_dir="$1"
shift

. .helpers/changelog/venv/bin/activate

echo "## [Unreleased]"

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder "$@"
)

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat

