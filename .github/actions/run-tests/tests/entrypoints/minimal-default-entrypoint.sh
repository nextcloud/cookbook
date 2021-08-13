#!/bin/bash


if [ `whoami` = root ]; then
	
	echo "Setting uid and gid to $RUNNER_UID/$RUNNER_GID"
	usermod -u $RUNNER_UID runner
	groupmod -g $RUNNER_GID runner
	
fi

exec "$@"
