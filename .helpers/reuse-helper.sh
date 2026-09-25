#!/bin/bash

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

# 2026 Nextcloud cookbook contributors

year=$(date '+%Y')
users="Nextcloud cookbook contributors"
license="AGPL-3.0-only OR AGPL-3.0-or-later"

while [ $# -gt 0 ]
do
	case "$1" in
		--year)
			year="$2"
			shift
			;;
		--users)
			users="$2"
			shift
			;;
		--license)
			license="$2"
			shift
			;;
		--)
			shift
			break
			;;
		*)
			echo "Unknown option: $1"
			exit 1
			;;
	esac
	shift
done

reuse annotate --year "$year" \
	--copyright "$users" \
	--license "$license" \
	--fallback-dot-license \
	--skip-existing \
	"$@"
