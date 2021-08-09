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
	
	if [ -n "$DEBUG_MODE" ]; then
		echo "Activating debugging mode in container"
		
		if [ -z "$DEBUG_PORT" ]; then
			DEBUG_PORT=9000
		fi
		
		if [ -z "$DEBUG_HOST" ]; then
			DEBUG_HOST='172.17.0.1'
		fi
		
		if [ -z "$DEBUG_UPON_ERROR" ]; then
			DEBUG_UPON_ERROR=default
		fi
		
		if [ -z "$DEBUG_START_MODE" ]; then
			DEBUG_START_MODE=default
		fi
		
		if [ -z "$DEBUG_TRACE_FORMAT" ]; then
			DEBUG_TRACE_FORMAT=1
		fi
		
		mode="develop,coverage,$DEBUG_MODE"
		
		cat >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini <<- EOF
			
			xdebug.mode=$mode
			xdebug.start_with_request = $DEBUG_START_MODE
			xdebug.start_upon_error=$DEBUG_UPON_ERROR
			xdebug.client_port = $DEBUG_PORT
			xdebug.client_host = $DEBUG_HOST
			xdebug.trace_format = $DEBUG_TRACE_FORMAT
			EOF
		
	fi
	
	echo "Running the main script as user runner"
	exec sudo -u runner -E "$@"
fi

exec "$@"
