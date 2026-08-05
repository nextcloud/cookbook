#! /bin/sh -e

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

#set -x

cd /var/www/html
php occ "$@"

exit $?

