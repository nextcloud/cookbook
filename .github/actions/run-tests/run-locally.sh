#! /bin/bash -e

print_help() {
	cat << EOF
Run the unittests

Possible options:
  --pull                            Force pulling of the latest images from docker hub.
  --create-images                   Force creation of custom docker images locally used for testing
  --create-images-if-needed         Only build those images that are not existing currently
  --push-images                     Push images to docker. Not yet working
  --start-helpers                   Start helper containers (database, http server)
  --shutdown-helpers                Shut down all containers running
  --setup-environment <BRANCH>      Setup a development environment in current folder. BRANCH dictates the branch of the server to use (e.g. stable20).
  --drop-environment                Reset the development environment and remove any files from it.
  --create-env-dump                 Create a backup from the environment. This allows fast recovery during test setup.
  --create-plain-dump <NAME>        Create a backup of the environment before the cookbook app is installed. This only works with --setup-environment. <NAME> is the name of the dump.
  --restore-env-dump                Restore an environment from a previous backup.
  --drop-env-dump                   Remove a backup from an environment
  --overwrite-env-dump              Allow to overwrite a backup of an environment
  --env-dump-path <PATH>            The name of the environment to save. Multiple environment backups are possible.
  --copy-env-dump <SRC> <DST>       Copy the environment dump in <SRC> to <DST>
  --list-env-dumps                  List all environment dumps stored in the docker volume
  --run-code-checker                Run the cod checker
  --run-unit-tests                  Run only the unit tests
  --run-integration-tests           Run only the integration tests
  --extract-code-coverage           Output the code coverage reports into the folder volumes/coverage/.
  --install-composer-deps           Install composer dependencies
  --build-npm                       Install and build js packages
  -q / --quick                      Test in quick mode. This will not update the permissions on successive (local) test runs.
  --debug                           Enable step debugging during the testing
  --debug-port <PORT>               Select the port on the host machine to attach during debugging sessions using xdebug (default 9000)
  --debug-host <HOST>               Host to connect the debugging session to (default to local docker host)
  --debug-up-error                  Enable the debugger in case of an error (see xdebug's start_upon_error configuration)
  --debug-start-with-request <MODE> Set the starting mode of xdebug to <MODE> (see xdebug's start_with_request configuration)
  --xdebug-log-level <LEVEL>        Set the log level of xdebug to <LEVEL>
  --enable-tracing                  Enable the tracing feature of xdebug
  --trace-format <FORMAT>           Set the trace format to the <FORMAT> (see xdebug's trace_format configuration)
  --enable-profiling                Enable the profiling function of xdebug
  --help                            Show this help screen
  --                                Pass any further parameters to the phpunit program
  
  --prepare <BRANCH>                Prepare the system for running the unit tests. This is a shorthand for
                                      --pull --create-images-if-needed --start-helpers --setup-environment <BRANCH> --create-env-dump --create-plain-dump plain
  --run-tests                       Run both unit as well as integration tests and code checking
  --run                             Run the unit tests themselves. This is a shorthand for
                                      --restore-env-dump --run-tests --extract-code-coverage
  
  You can provide parameters for phpunit after a double dash (--). For example you can filter like so:
    run-locally.sh <other options> -- --filter <filter string>

  The following environment variables are taken into account:
    INPUT_DB            Defines which database to use for the integration tests. Can be mysql, pgsql or sqlite. Defaults to mysql.
    PHP_VERSION         Defines the PHP version to use, e.g. 7.4, 8. Defaults to 7.
    HTTP_SERVER         Defines the HTTP deamon to use. Possible values are apache and nginx. Defaults to apache.
    CI                  If the script is run in CI environment
    ALLOW_FAILURE       Defines if the script is allowed to fail. Possible values are true and false (default)
EOF
}

list_env_dumps() {
	echo "Available environemnt dumps:"
	find volumes/dumps -maxdepth 1 -mindepth 1 -type d | sed 's@^volumes/dumps/@ - @'
	exit 0
}

pull_images() {
	echo 'Pulling pre-built images.'
	docker-compose pull --quiet
	
	if docker pull "nextcloudcookbook/testci:php$PHP_VERSION"; then
		docker tag "nextcloudcookbook/testci:php$PHP_VERSION" cookbook_unittesting_dut
	fi
	
	echo 'Pulling images finished.'
}

build_images() {
	pull_images
	
	echo 'Building the images.'
	local PROGRESS=''
	if [ -n "$CI" ]; then
		PROGRESS='--progress plain'
	fi
	
	docker-compose build --pull --force-rm $PROGRESS \
		--build-arg PHPVERSION=$PHP_VERSION \
		dut occ php fpm
	docker-compose build --pull --force-rm mysql
	echo 'Building images finished.'
}

is_image_exists() {
# 	TODO Further Checks needed?
	lines=$(docker images | awk '$1 == "cookbook_unittesting_dut"' | wc -l)
	test $lines -gt 0
}

push_images() {
	echo "Retagging docker image"
	docker tag cookbook_unittesting_dut "nextcloudcookbook/testci:php$PHP_VERSION"
	docker push "nextcloudcookbook/testci:php$PHP_VERSION"
}

create_file_structure() {
	echo 'Creating the required file structure for the volumes'
	mkdir -p volumes/{mysql,postgres,nextcloud,cookbook,data,dumps,coverage,www,output}
}

start_helpers(){
	echo 'Starting helper containers.'
	
	echo 'Starting the database.'
	case "$INPUT_DB" in
		mysql)
			docker-compose up -d mysql
			
			# Wait for service become available
			local i
			for i in `seq 1 20`
			do
				local state=$(docker inspect --format "{{.State.Health.Status}}" cookbook_unittesting_mysql_1)
				
				if [ "$state" = 'healthy' ]; then
					break
				fi
				
				sleep 2.5
			done
			
			if [ $i -eq 20 ]; then
				echo 'Error: Could not connect with mysql service container'
				exit 1
			fi
			;;
		pgsql)
			docker-compose up -d postgres
			
			# Wait for the service to become available
			local i
			for i in `seq 1 10`
			do
				echo '\q' | call_postgres && break || true
				sleep 5
			done
			
			if [ $i -eq 10 ]; then
				echo 'Could not connect with postgres service container'
				exit 1
			fi
			;;
		sqlite)
			;;
		*)
			echo "Unknown database given in INPUT_DB: $INPUT_DB"
			exit 1
			;;
	esac
	echo 'Databse is started.'
	
	echo 'Service containers are started.'
}

start_helpers_post() {
	echo "Starting helper containers (post-install)."
	
	docker-compose up -d www
	
	docker-compose up -d fpm
	
	case "$HTTP_SERVER" in
		apache)
			docker-compose up -d apache
			;;
		nginx)
			docker-compose up -d nginx
			;;
		*)
			echo "No known HTTP server requested: $HTTP_SERVER. Exiting"
			exit 1
			;;
	esac
	
	echo 'Finished installing helper containers (post-install).'
}

shutdown_helpers(){
	echo 'Shutting down all containers.'
	docker-compose down -v
}

setup_server(){
	echo 'Setup of the server environment.'
	
	echo "Checking out nextcloud server repository"
	git clone --depth=1 --branch "$ENV_BRANCH" https://github.com/nextcloud/server volumes/nextcloud
	
	echo "Updating the submodules"
	pushd volumes/nextcloud
	git submodule update --init
	popd
	
	echo 'Creating cookbook folder for later bind-merge'
	pushd volumes/nextcloud
	mkdir apps/cookbook data
	popd
	
	echo "Installing Nextcloud server instance"
	case "$INPUT_DB" in
		mysql)
			docker-compose run --rm -T occ \
				maintenance:install \
				--database mysql \
				--database-host mysql \
				--database-name "$MYSQL_DATABASE" \
				--database-user "$MYSQL_USER" \
				--database-pass "$MYSQL_PASSWORD" \
				--admin-user admin \
				--admin-pass admin
			;;
		pgsql)
			docker-compose run --rm -T occ \
				maintenance:install \
				--database pgsql \
				--database-host postgres \
				--database-name "$POSTGRES_DB" \
				--database-user "$POSTGRES_USER" \
				--database-pass "$POSTGRES_PASSWORD" \
				--admin-user admin \
				--admin-pass admin
			;;
		sqlite)
			docker-compose run --rm -T occ \
				maintenance:install \
				--database sqlite \
				--admin-user admin \
				--admin-pass admin
			;;
		*)
			echo "Unknown database: $INPUT_DB"
			exit 1
			;;
	esac
	
	echo 'Server installed successfully.'
}

setup_app () {
	echo 'Installing cookbook app.'
	
	if [ "$ALLOW_FAILURE" = 'true' ]; then
		echo 'Add exception for app to install even if not officially supported'
		cat scripts/enable_app_install_script.php | docker-compose run --rm -T php
	fi
	
	echo "Synchronizing the cookbook codebase to volume"
	rsync -a ../../../ volumes/cookbook --exclude /.git --exclude /.github/actions/run-tests/volumes --exclude /node_modules/ --delete --delete-delay
	
	echo "Ensuring the appinfo is present"
	pushd volumes/cookbook > /dev/null
	make appinfo/info.xml
	popd > /dev/null
	
	echo "Activating the cookbook app in the server"
	docker-compose run --rm -T occ app:enable cookbook
	
	echo 'Installation of cookbook app finished.'
}

drop_environment(){
	echo 'Dropping the environment.'
	
	# Drop all tables
	case "$INPUT_DB" in
		mysql)
			local tables=$(echo "SHOW TABLES;" | call_mysql | tail -n +2)
			if [ -n "$tables" ]; then
				echo "$tables" | sed 's@.*@DROP TABLE \0;@' | call_mysql
			fi
			;;
		pgsql)
			call_postgres <<- EOF
				DROP SCHEMA public CASCADE;
				CREATE SCHEMA public;
				GRANT ALL ON SCHEMA public TO $POSTGRES_USER;
				GRANT ALL ON SCHEMA public TO public;
				EOF
			;;
	esac
	
	# Remove any data in the volumes
	rm -rf volumes/{data,nextcloud}/{*,.??*}
	
	echo 'Finished dropping the environment.'
}

dump_new_dump () {
	
	echo "Creating backup of the environemnt to $1"
	
	if [ -d "volumes/dumps/$1" -a "$2" != 'y' ]; then
		echo "Cannot create dump $1 as it is already existing."
		exit 1
	fi
	
	mkdir -p "volumes/dumps/$1/"{data,nextcloud,sql}
	
	# Dump data
	echo "Saving data files"
	rsync $RSYNC_PARAMS volumes/data/ "volumes/dumps/$1/data"
	
	# Dump server files
	echo "Saving server files"
	rsync $RSYNC_PARAMS volumes/nextcloud/ "volumes/dumps/$1/nextcloud" \
		--exclude /.git
	
	# Dump SQL
	create_db_dump "$1"
	
	echo 'Finished backup creation.'
}

restore_env_dump() {
	echo 'Restoring the environment from backup.'
	
	if [ ! -d "volumes/dumps/$ENV_DUMP_PATH" ]; then
		echo "Dump $ENV_DUMP_PATH was not found. Cannot restore it."
		exit 1
	fi
	
	# Restore data
	echo "Restoring data files"
	rsync $RSYNC_PARAMS "volumes/dumps/$ENV_DUMP_PATH/data/" volumes/data/
	
	# Restore server files
	if [ "$QUICK_MODE" = y ]; then
		echo 'Quick mode activated. Does not restore server core files.'
	else
		echo "Restoring server files"
		rsync $RSYNC_PARAMS "volumes/dumps/$ENV_DUMP_PATH/nextcloud/" volumes/nextcloud/
	fi
	
	# Restore DB dump
	case "$INPUT_DB" in
		mysql)
			echo "Restoring MySQL database from dump"
			cat "volumes/dumps/$ENV_DUMP_PATH/sql/dump.sql" | docker-compose exec -T mysql mysql -u root -p"$MYSQL_ROOT_PASSWORD"
			;;
		pgsql)
			echo "Restoring Postgres database from dump"
			call_postgres <<- EOF
				DROP SCHEMA public CASCADE;
				CREATE SCHEMA public;
				GRANT ALL ON SCHEMA public TO $POSTGRES_USER;
				GRANT ALL ON SCHEMA public TO public;
				EOF
			cat "volumes/dumps/$ENV_DUMP_PATH/sql/dump.sql" | call_postgres
			;;
		sqlite)
			echo "Restoring of sqlite not required as already covered by data restoring."
			;;
		*)
			echo "Unknown database type: $INPUT_DB. Aborting."
			exit 1
			;;
	esac
	
	echo 'Restoring the environment from backup finished.'
}

drop_env_dump() {
	echo 'Dropping backup from environment.'
	rm -rf "volumes/dumps/$ENV_DUMP_PATH"
}

copy_environment() {
	echo "Copying dump $COPY_ENV_SRC to new name $COPY_ENV_DST"
	
	rm -rf "volumes/dumps/$COPY_ENV_DST"
	cp -a "volumes/dumps/$COPY_ENV_SRC" "volumes/dumps/$COPY_ENV_DST"
}

run_tests() {
	
	PARAMS=''
	
	if [ $RUN_UNIT_TESTS = 'y' ]; then
		PARAMS+=' --run-unit-tests'
	fi
	
	if [ $RUN_INTEGRATION_TESTS = 'y' ]; then
		PARAMS+=' --run-integration-tests'
	fi
	
	if [ $EXTRACT_CODE_COVERAGE = 'y' ]; then
		PARAMS+=' --create-coverage-report'
	fi
	
	if [ $INSTALL_COMPOSER_DEPS = 'y' ]; then
		PARAMS+=' --install-composer-deps'
	fi
	
	if [ $BUILD_NPM = 'y' ]; then
		PARAMS+=' --build-npm'
	fi
	
	if [ "$RUN_CODE_CHECKER" = 'y' ]; then
		PARAMS+=' --run-code-checker'
	fi
	
	echo "Staring container to run the unit tests."
	echo "Parameters for container: $PARAMS"
	echo "Additional parameters for phpunit: $@"
	docker-compose run --rm -T dut $PARAMS -- "$@"
	echo 'Test runs finished.'
}

function create_db_dump() {
	case "$INPUT_DB" in
		mysql)
			echo "Dumping MySQL database"
			docker-compose exec -T mysql mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" --add-drop-database --databases "$MYSQL_DATABASE" > "volumes/dumps/$1/sql/dump.sql"
			;;
		pgsql)
			echo "Dumping Postgres database"
			docker-compose exec -T postgres pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB" --clean --if-exists > "volumes/dumps/$1/sql/dump.sql"
			;;
		sqlite)
			echo "No sqlite dump is generates as it is saved with the regular files."
			;;
		*)
			echo "Unknown database type: $INPUT_DB. Aborting."
			exit 1
			;;
	esac
}

function call_mysql() {
	docker-compose exec -T mysql mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"
}

function call_postgres() {
	#docker-compose exec -T postgres psql -t nc_test tester
	docker-compose exec -T postgres psql -U "$POSTGRES_USER" "$POSTGRES_DB" -v 'ON_ERROR_STOP=1' --quiet "$@"
}

##### Parameters as extracted from the CLI

DOCKER_PULL=n
CREATE_IMAGES=n
CREATE_IMAGES_IF_NEEDED=n
PUSH_IMAGES=n
START_HELPERS=n
SHUTDOWN_HELPERS=n
SETUP_ENVIRONMENT=n
DROP_ENVIRONMENT=n
CREATE_ENV_DUMP=n
CREATE_PLAIN_DUMP=''
RESTORE_ENV_DUMP=n
DROP_ENV_DUMP=n
OVERWRITE_ENV_DUMP=n
RUN_CODE_CHECKER=n
RUN_UNIT_TESTS=n
RUN_INTEGRATION_TESTS=n
EXTRACT_CODE_COVERAGE=n
INSTALL_COMPOSER_DEPS=n
BUILD_NPM=n
QUICK_MODE=n

DEBUG=n
DEBUG_PORT='9000'
DEBUG_HOST='172.17.0.1'
DEBUG_UPON_ERROR='default'
DEBUG_START_MODE='yes'
DEBUG_MODE_STEP=n
DEBUG_MODE_TRACE=n
DEBUG_MODE_PROFILE=n
DEBUG_TRACE_FORMAT=1

ENV_BRANCH=stable20
ENV_DUMP_PATH=default
PHP_VERSION="${PHP_VERSION:-7}"

COPY_ENV_SRC=''
COPY_ENV_DST=''

source mysql.env
source postgres.env

RSYNC_PARAMS="--delete --delete-delay --delete-excluded --archive"

##### Extract CLI parameters into internal variables

if [ $# -eq 0 ]; then
	echo "No parameters give. Please specify the operation to be carried out. See $0 --help"
	exit 0
fi

while [ $# -gt 0 ]
do
	case "$1" in
		--help|-h)
			print_help
			exit 0
			;;
		--pull)
			DOCKER_PULL=y
			;;
		--create-images)
			CREATE_IMAGES=y
			;;
		--create-images-if-needed)
			CREATE_IMAGES_IF_NEEDED=y
			;;
		--push-images)
			PUSH_IMAGES=y
			;;
		--start-helpers)
			START_HELPERS=y
			;;
		--shutdown-helpers)
			SHUTDOWN_HELPERS=y
			;;
		--setup-environment)
			SETUP_ENVIRONMENT=y
			ENV_BRANCH="$2"
			shift
			;;
		--drop-environment)
			DROP_ENVIRONMENT=y
			;;
		--create-env-dump)
			CREATE_ENV_DUMP=y
			;;
		--create-plain-dump)
			CREATE_PLAIN_DUMP="$2"
			shift
			;;
		--restore-env-dump)
			RESTORE_ENV_DUMP=y
			;;
		--drop-env-dump)
			DROP_ENV_DUMP=y
			;;
		--overwrite-env-dump)
			OVERWRITE_ENV_DUMP=y
			;;
		--env-dump-path)
			ENV_DUMP_PATH="$2"
			shift
			;;
		--list-env-dumps)
			list_env_dumps
			exit 0
			;;
		--copy-env-dump)
			COPY_ENV_SRC="$2"
			COPY_ENV_DST="$3"
			shift 2
			;;
		--run-code-checker)
			RUN_CODE_CHECKER=y
			;;
		--run-tests)
			RUN_UNIT_TESTS=y
			RUN_INTEGRATION_TESTS=y
			;;
		--run-unit-tests)
			RUN_UNIT_TESTS=y
			;;
		--run-integration-tests)
			RUN_INTEGRATION_TESTS=y
			;;
		--extract-code-coverage)
			EXTRACT_CODE_COVERAGE=y
			;;
		--install-composer-deps)
			INSTALL_COMPOSER_DEPS=y
			;;
		--build-npm)
			BUILD_NPM=y
			;;
		--quick|-q)
			QUICK_MODE=y
			;;
		--filter)
			echo 'The --filter parameter is no longer supported. Please use it after --. See also the help (-h).'
			exit 1
			;;
		--debug)
			DEBUG_MODE_STEP=y
			;;
		--debug-port)
			DEBUG_PORT="$2"
			shift
			;;
		--debug-host)
			DEBUG_HOST="$2"
			shift
			;;
		--debug-up-error)
			DEBUG_UPON_ERROR=yes
			;;
		--debug-start-with-request)
			DEBUG_START_MODE="$2"
			shift
			;;
		--enable-tracing)
			DEBUG_MODE_TRACE=y
			;;
		--trace-format)
			DEBUG_TRACE_FORMAT="$2"
			shift
			;;
		--enable-profiling)
			DEBUG_MODE_PROFILE=y
			;;
		--xdebug-log-level)
			XDEBUG_LOG_LEVEL="$2"
			shift
			;;
		--prepare)
			DOCKER_PULL=y
			CREATE_IMAGES_IF_NEEDED=y
			START_HELPERS=y
			SETUP_ENVIRONMENT=y
			ENV_BRANCH="$2"
			CREATE_ENV_DUMP=y
			CREATE_PLAIN_DUMP=plain
			shift
			;;
		--run)
			RESTORE_ENV_DUMP=y
			RUN_CODE_CHECKER=y
			RUN_UNIT_TESTS=y
			RUN_INTEGRATION_TESTS=y
			EXTRACT_CODE_COVERAGE=y
			;;
		--)
			shift
			break
			;;
		*)
			echo "Unknown parameter $1. Exiting."
			print_help
			exit 1
			;;
	esac
	
	shift
done

export PS4='+ $0:$LINENO '
# set -x

##### Do some sanity checks

echo 'Doing some parameter checks.'

if [ $CREATE_IMAGES = 'y' -a $CREATE_IMAGES_IF_NEEDED = 'y' ]; then
	echo "You have both specified --create-images and --create-images-if-needed. These are exclusive options."
	exit 1
fi

if [ -z "$INPUT_DB" ]; then
	echo "No database configured. Falling back to mysql"
	export INPUT_DB=mysql
fi

if echo "$ENV_DUMP_PATH" | grep ' ' > /dev/null; then
	echo "The dump path '$ENV_DUMP_PATH' contains invalid characters. Please adjust"
	exit 1
fi

if [ "$SETUP_ENVIRONMENT" = 'n' -a -n "$CREATE_PLAIN_DUMP" ]; then
	echo "Cannot create a plain dump without reinstalling the environment."
	exit 1
fi

if [ -z "$HTTP_SERVER" ]; then
	echo "No HTTP server was given. Falling back to apache"
	HTTP_SERVER=apache
fi

if [ -z "$RUNNER_UID" ]; then
	RUNNER_UID=`id -u`
fi
export RUNNER_UID

if [ -z "$RUNNER_GID" ]; then
	RUNNER_GID=`id -g`
fi
export RUNNER_GID

if [ "$DEBUG_MODE_STEP" = y -o "$DEBUG_MODE_TRACE" = y -o "$DEBUG_MODE_PROFILE" = y ]; then
	DEBUG=y
	
	DEBUG_MODE=''
	
	if [ "$DEBUG_MODE_STEP" = y ]; then
		DEBUG_MODE=',debug'
	fi
	
	if [ "$DEBUG_MODE_TRACE" = y ]; then
		DEBUG_MODE="$DEBUG_MODE,trace"
	fi
	
	if [ "$DEBUG_MODE_PROFILE" = y ]; then
		DEBUG_MODE="$DEBUG_MODE,profile"
	fi
	
	DEBUG_MODE=$(echo "$DEBUG_MODE" | cut -c 2-)
fi

export DEBUG_PORT DEBUG_HOST DEBUG_UPON_ERROR DEBUG_START_MODE DEBUG_MODE DEBUG_TRACE_FORMAT XDEBUG_LOG_LEVEL

if [ -z "$COPY_ENV_SRC" -a -n "$COPY_ENV_DST" ]; then
	echo "You need to specify a source environment name. Nothing found."
	exit 1
fi

if [ -n "$COPY_ENV_SRC" ]; then
	if [ -z "$COPY_ENV_DST" ]; then
		echo "You need to specify a destination environment name."
		exit 1
	fi
	
	if [ ! -d "volumes/dumps/$COPY_ENV_SRC" ]; then
		echo "The source environment $COPY_ENV_SRC is not found."
		exit 1
	fi
	
	if [ -d "volumes/dumps/$COPY_ENV_DST" -a "$OVERWRITE_ENV_DUMP" != y ]; then
		echo "The destination env dump $COPY_ENV_DST is already existing. No overwrite was specified. Aborting."
		exit 1
	fi
fi

export QUICK_MODE

echo "Using PHP version $PHP_VERSION"

##### Start processing the tasks at hand


trap 'catch $? $LINENO' EXIT

catch()
{
	echo '::set-output name=silent-fail::false';
	
	if [ "$1" != '0' ]; then
		echo "::error line=$LINENO::Error during the test run: $1"
		
		if [ "$ALLOW_FAILURE" = 'true' -a "$CI" = 'true' ]; then
			echo '::set-output name=silent-fail::true'
			exit 0
		else
			exit $1
		fi
	else
		echo 'Terminated successfully'
	fi
}

echo 'Starting process'

if [ -n "$COPY_ENV_SRC" ]; then
	copy_environment
fi

if [ $DOCKER_PULL = 'y' ]; then
	pull_images
fi

if [ $CREATE_IMAGES = 'y' ]; then
	build_images
fi

if [ $CREATE_IMAGES_IF_NEEDED = 'y' ]; then
	if ! is_image_exists; then
		build_images
	fi
fi

if [ $PUSH_IMAGES = 'y' ]; then
	if ! is_image_exists; then
		echo "Cannot push images as it does not exist"
		exit 1
	fi
	push_images
fi

create_file_structure

if [ $START_HELPERS = 'y' ]; then
	start_helpers
fi

if [ $DROP_ENVIRONMENT = 'y' ]; then
	drop_environment
fi

if [ $SETUP_ENVIRONMENT = 'y' ]; then
	setup_server
	
	if [ -n "$CREATE_PLAIN_DUMP" ]; then
		dump_new_dump "$CREATE_PLAIN_DUMP" "$OVERWRITE_ENV_DUMP"
	fi
	
	setup_app
fi

if [ $DROP_ENV_DUMP = 'y' ]; then
	drop_env_dump
fi

if [ $CREATE_ENV_DUMP = 'y' ]; then
	dump_new_dump "$ENV_DUMP_PATH" "$OVERWRITE_ENV_DUMP"
fi

if [ $RESTORE_ENV_DUMP = 'y' ]; then
	restore_env_dump
fi

if [ $START_HELPERS = 'y' ]; then
	start_helpers_post
fi

if [ $RUN_UNIT_TESTS = 'y' -o $RUN_INTEGRATION_TESTS = 'y' ]; then
	run_tests "$@"
fi

if [ $SHUTDOWN_HELPERS = 'y' ]; then
	shutdown_helpers
fi

echo "Processing finished"
