#!/bin/bash -e

# set -x

trap 'catch $? $LINENO' EXIT

catch()
{
	echo '::set-output name=silent-fail::false';
	
	if [ "$1" != '0' ]; then
# 		echo "::error line=$LINENO::Error during the test run: $1"
		
		if [ "$ALLOW_FAILURE" = 'true' ]; then
			echo '::set-output name=silent-fail::true'
			exit 0
		else
			exit $1
		fi
	fi
}

printCI() {
	if [ "$CI" = 'true' ]; then
		echo "$@"
	fi
}

RUN_UNIT_TESTS=n
RUN_INTEGRATION_TESTS=n
RUN_MIGRATION_TESTS=n
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
		--run-migration-tests)
			RUN_MIGRATION_TESTS=y
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

printCI "::group::Test prepatation in container"

echo "Synchronizing cookbook codebase"
rsync -a /cookbook/ custom_apps/cookbook/ --delete --delete-delay --delete-excluded --exclude /.git --exclude /.github/actions/run-tests/volumes --exclude /docs --exclude /node_modules/

pushd custom_apps/cookbook

if [ $INSTALL_COMPOSER_DEPS = 'y' ]; then
	echo "Checking composer compatibility"
	composer check-platform-reqs

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

printCI "::endgroup::"

PARAM_COVERAGE_UNIT='--log-junit /coverage/junit.xml --log-teamcity /coverage/teamcity.log'
PARAM_COVERAGE_INTEGRATION='--log-junit /coverage/junit-integration.xml --log-teamcity /coverage/teamcity.integration.log'
PARAM_COVERAGE_MIGRATION='--log-junit /coverage/junit-migration.xml --log-teamcity /coverage/teamcity.migration.log'

if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	rm -rf /coverage/tmp
	mkdir /coverage/tmp
	PARAM_COVERAGE_UNIT+=' --coverage-clover /coverage/tmp/coverage.unit.xml --coverage-html /coverage/tmp/coverage-unit'
	PARAM_COVERAGE_INTEGRATION+=' --coverage-clover /coverage/tmp/coverage.integration.xml --coverage-html /coverage/tmp/coverage-integration'
	PARAM_COVERAGE_MIGRATION+=' --coverage-clover /coverage/tmp/coverage.migration.xml --coverage-html /coverage/tmp/coverage-migration'
fi

if [ $RUN_CODE_CHECKER = 'y' ]; then
	printCI "::group::Code checker"
	echo 'Running the code checker'
	if ! ./occ app:check-code cookbook; then
		echo '::error ::The code checker rejected the code base. See the logs of the action for further details.'
		exit 1
	fi
	echo 'Code checker finished'
	printCI "::endgroup::"
fi

pushd custom_apps/cookbook > /dev/null

make appinfo/info.xml

FAILED=0

if [ $RUN_UNIT_TESTS = 'y' ]; then
	echo 'Starting unit testing.'
	/phpunit -c phpunit.xml $PARAM_COVERAGE_UNIT "$@" || { FAILED=$?; true; }
	echo 'Unit testing done.'
fi

if [ $RUN_INTEGRATION_TESTS = 'y' ]; then
	echo 'Starting integration testing.'
	/phpunit -c phpunit.integration.xml $PARAM_COVERAGE_INTEGRATION "$@" || { FAILED=$?; true; }
	echo 'Integration testing done.'
fi

if [ $RUN_MIGRATION_TESTS = 'y' ]; then
	echo 'Starting migration testing.'
	/phpunit -c phpunit.migration.xml $PARAM_COVERAGE_MIGRATION "$@" || { FAILED=$?; true; }
	echo 'Migration testing done.'
fi

popd > /dev/null

printCI "::group::Postprocessing output"

if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	echo 'Patching style in coverage report'
	cd /coverage/tmp
	for f in coverage-unit coverage-integration coverage-migration
	do
		if [ -f "$f/_css/style.css" ]; then
			sed -i -f /helper/style.sed "$f/_css/style.css"
		fi
	done
	
	NAME="run-$(date "+%Y-%m-%d_%H-%M-%S")"
	echo "Moving coverage report to final destination $NAME"
	cd /coverage
	mv tmp "$NAME"
	ln -snf "$NAME" ./latest
fi

printCI "::endgroup::"

if [ $FAILED != 0 ]; then
	echo "Failing as testing failed"
	exit $FAILED
fi
