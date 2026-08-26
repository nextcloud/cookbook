#!/bin/bash

# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later


if [ `whoami` = root ]; then
	
	echo "Setting uid and gid to $RUNNER_UID/$RUNNER_GID"
	usermod -u $RUNNER_UID runner
	groupmod -g $RUNNER_GID runner

	docker_gid=$(stat --printf='%g' /var/run/docker.sock)
	groupadd -g $docker_gid docker-host
	usermod -aG docker-host runner
	
fi

exec "$@"
