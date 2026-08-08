#!/bin/bash

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

dir=$(dirname "$0")
TOKEN=$(cat "$dir/token.file")

curl -H "Authorization: Token $TOKEN" "$@"
