#!/bin/sh -e

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

localdir="$(dirname "$0")"

versions_dir="$localdir/../../.changelog/versions"

. "$localdir/venv/bin/activate"

changelog="$localdir/../../CHANGELOG.md"

cat "$localdir/../../.changelog/prefix.md" > "$changelog"

echo "## [Unreleased]" >> "$changelog"

(
	export PYTHONPATH="$(pwd)/.helpers/changelog"
	python -m changelog_builder "$@"
) >> "$localdir/../../CHANGELOG.md"

find "$versions_dir" -type f | sort -Vr | xargs -n 1 cat >> "$localdir/../../CHANGELOG.md"

