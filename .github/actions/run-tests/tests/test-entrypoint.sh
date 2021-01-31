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
