#!/bin/bash -e

# set -x

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
		--)
			# Stop processing here. The rest goes to phpunit directly
			shift
			break
			;;
	esac
	shift
done

PARAM_COVERAGE=''
if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	rm -rf /dumps/tmp
	mkdir /dumps/tmp
	PARAM_COVERAGE='--coverage-clover /dumps/tmp/coverage.integration.xml --coverage-html /dumps/tmp/coverage-integration'
fi

cd nextcloud

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
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml $PARAM_COVERAGE "$@"
	echo 'Unit testing done.'
fi

if [ $RUN_INTEGRATION_TESTS = 'y' ]; then
	echo 'Starting integration testing.'
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml $PARAM_COVERAGE "$@"
	echo 'Integration testing done.'
fi

popd

if [ $CREATE_COVERAGE_REPORT = 'y' ]; then
	echo 'Moving coverage report to final destination'
	cd /dumps
	rm -rf latest
	mv tmp latest
	cp -a latest run-$(date "+%Y-%m-%d_%H-%M-%S")
fi
