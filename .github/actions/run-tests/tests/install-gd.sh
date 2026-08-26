#!/bin/bash -e

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

function configure_gd_normal ()
{
	docker-php-ext-configure gd --with-freetype --with-jpeg
	return $?
}

function configure_gd_without ()
{
	docker-php-ext-configure gd
	return $?
}

function configure_gd()
{
	if [ "$1" = "7.2" -o "$1" = "7.3" ]; then
		configure_gd_without
		return $?
	else
		configure_gd_normal
		return $?
	fi
}

echo 'Installing PHP gd'
configure_gd "$1" > /dev/null
docker-php-ext-install -j$(nproc) gd > /dev/null
