#! /bin/bash

if [ `whoami` = root ]; then
	
	if [ -z "$QUICK_MODE" -o "$QUCK_MODE" = n ]; then
		echo "Setting uid and gid to $RUNNER_UID/$RUNNER_GID"
		usermod -u $RUNNER_UID runner
		groupmod -g $RUNNER_GID runner
		
		echo "Changing ownership of files to runner"
		chown -R runner: /nextcloud
	else
		echo "Quick mode activated. No permission update is carried out"
	fi
	
	if [ -n "$DEBUG" -a "$DEBUG" = y ]; then
		echo "Activating step debugging mode in container"
		
		if [ -n "$DEBUG_PORT" ]; then
			DEBUG_PORT=9000
		fi
		
		if [ -n "$DEBUG_HOST" ]; then
			DEBUG_HOST='172.17.0.1'
		fi
		
		if [ -n "$DEBUG_UPON_ERROR" ]; then
			DEBUG_UPON_ERROR=default
		fi
		
		if [ -n "$DEBUG_START_MODE" ]; then
			DEBUG_START_MODE=default
		fi
		
		cat >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini <<- EOF
			
			xdebug.mode=develop,coverage,debug
			xdebug.start_upon_error=$DEBUG_UPON_ERROR
			xdebug.client_port = $DEBUG_PORT
			xdebug.client_host = $DEBUG_HOST
			#xdebug.start_with_request = $DEBUG_START_MODE
			EOF
	fi
	
	echo "Running the main script as user runner"
	exec sudo -u runner -E "$@"
fi

exec "$@"
