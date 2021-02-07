#! /bin/bash

if [ `whoami` = root ]; then
	echo "Setting uid and gid to $RUNNER_UID/$RUNNER_GID"
	usermod -u $RUNNER_UID runner
	groupmod -g $RUNNER_GID runner
	
	echo "Changing ownership of files to runner"
	chown -R runner: /nextcloud
	
	echo "Running the main script as user runner"
	exec sudo -u runner -E "$0" "$@"
fi

exec "$@"
