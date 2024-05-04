#!/bin/sh

localdir="$(dirname "$0")"

versions_dir="$localdir/../../.changelog/versions"
shift

. .helpers/changelog/venv/bin/activate

echo "## [Unreleased]"

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder "$@"
)

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat > "$localdir/../../CHANGELOG.md"

