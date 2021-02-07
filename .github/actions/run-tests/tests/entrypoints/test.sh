#!/bin/bash -e

# set -x

if [ `whoami` = root ]; then
	echo "Setting uid and gid to $RUNNER_UID/$RUNNER_GID"
	usermod -u $RUNNER_UID runner
	groupmod -g $RUNNER_GID runner
	
	echo "Changing ownership of files to runner"
	chown -R runner: /nextcloud
	
	echo "Running the main script as user runner"
	exec sudo -u runner -E "$0" "$@"
fi

trap 'catch $? $LINENO' EXIT

catch()
{
	echo '::set-output name=silent-fail::false';
	
	if [ "$1" != '0' ]; then
		echo "::error line=$LINENO::Error during the test run: $1"
		
		if [ "$ALLOW_FAILURE" = 'true' ]; then
			echo '::set-output name=silent-fail::true'
			exit 0
		else
			exit $1
		fi
	fi
}

RUN_UNIT_TESTS=n
RUN_INTEGRATION_TESTS=n
CREATE_COVERAGE_REPORT=n
RUN_CODE_CHECKER=n
INSTALL_COMPOSER_DEPS=n
BUILD_NPM=n

while [ $# -gt 0 ]
do
	case "$1" in
		--run-unit-tests)
			RUN_UNIT_TESTS=y
			;;
		--run-integration-tests)
			RUN_INTEGRATION_TESTS=y
			;;
		--create-coverage-report)
			CREATE_COVERAGE_REPORT=y
			;;
		--run-code-checker)
			RUN_CODE_CHECKER=y
			;;
		--install-composer-deps)
			INSTALL_COMPOSER_DEPS=y
			;;
		--build-npm)
			BUILD_NPM=y
			;;
		--)
			# Stop processing here. The rest goes to phpunit directly
			shift
			break
			;;
		*)
			echo "Unknown option found: $1"
			exit 1
			;;
	esac
	shift
done

echo "Synchronizing cookbook codebase"
rsync -a /cookbook/ apps/cookbook/ --delete --delete-delay --exclude /.git --exclude /.github/actions/run-tests/volumes --exclude /docs

pushd apps/cookbook

if [ $INSTALL_COMPOSER_DEPS = 'y' ]; then
	echo "Installing/updating composer dependencies"
	composer install
fi

if [ $BUILD_NPM = 'y' ]; then
	echo 'Installing NPM packages'
	npm install
	
	echo 'Building JS folder'
	npm run build
fi

popd

PARAM_COVERAGE_UNIT=''
PARAM_COVERAGE_INTEGRATION=''
if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	rm -rf /coverage/tmp
	mkdir /coverage/tmp
	PARAM_COVERAGE_UNIT='--coverage-clover /coverage/tmp/coverage.unit.xml --coverage-html /coverage/tmp/coverage-unit'
	PARAM_COVERAGE_INTEGRATION='--coverage-clover /coverage/tmp/coverage.integration.xml --coverage-html /coverage/tmp/coverage-integration'
fi

if [ $RUN_CODE_CHECKER = 'y' ]; then
	echo 'Running the code checker'
	if ! ./occ app:check-code cookbook; then
		echo '::error ::The code checker rejected the code base. See the logs of the action for further details.'
		exit 1
	fi
	echo 'Code checker finished'
fi

pushd apps/cookbook

if [ $RUN_UNIT_TESTS = 'y' ]; then
	echo 'Starting unit testing.'
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml $PARAM_COVERAGE_UNIT "$@"
	echo 'Unit testing done.'
fi

if [ $RUN_INTEGRATION_TESTS = 'y' ]; then
	echo 'Starting integration testing.'
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml $PARAM_COVERAGE_INTEGRATION "$@"
	echo 'Integration testing done.'
fi

popd

if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	echo 'Patching style in coverage report'
	cd /coverage/tmp
	for f in coverage-unit coverage-integration
	do
		if [ -f "$f/_css/style.css" ]; then
			patch -i /helper/style.patch "$f/_css/style.css"
		fi
	done
	
	NAME="run-$(date "+%Y-%m-%d_%H-%M-%S")"
	echo "Moving coverage report to final destination $NAME"
	cd /coverage
	mv tmp "$NAME"
	ln -snf "$NAME" ./latest
fi
