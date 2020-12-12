#!/bin/bash -e

set -x

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

cd nextcloud

if [ ! "$1" = '--test-only' ]; then

	echo "Preparing the build system"

	echo 'Cloning the app code'
	mkdir -p apps/cookbook
	rsync -a /app/ apps/cookbook/ --delete --exclude /.git --exclude /build --exclude /.github

	echo 'Updating the submodules'
	git submodule update --init

	echo "Build the app"
	pushd apps/cookbook
	npm install
	make
	popd

	echo "Prepare database"

	function call_mysql()
	{
		mysql -u tester -h mysql -ptester_pass "$@"
	}

	function call_pgsql()
	{
		PGPASSWORD=tester_pass psql -h postgres "$@" nc_test tester
	}

	case "$INPUT_DB" in
		mysql)
			for i in `seq 1 10`
			do
				call_mysql -e 'SHOW PROCESSLIST;' && break || true
				sleep 5
			done
			if [ $i -eq 10 ]; then
				echo '::error ::Could not connect to mysql database'
				exit 1
			fi
			;;
		pgsql)
			for i in `seq 1 10`
			do
				call_pgsql -c '\q' && break || true
				sleep 5
			done
			if [ $i -eq 10 ]; then
				echo '::error ::Could not connect to postgres database'
				exit 1
			fi
			;;
		sqlite)
			;;
		*)
			echo "::warning ::No database specific initilization in test script. This might be a bug."
			;;
	esac

	echo "Initialize nextcloud instance"
	mkdir data

	case "$INPUT_DB" in
		mysql)
			./occ maintenance:install \
				--database mysql \
				--database-host mysql \
				--database-name nc_test \
				--database-user tester \
				--database-pass 'tester_pass' \
				--admin-user admin \
				--admin-pass admin
			;;
		pgsql)
			./occ maintenance:install \
				--database pgsql \
				--database-host postgres \
				--database-name nc_test \
				--database-user tester \
				--database-pass 'tester_pass' \
				--admin-user admin \
				--admin-pass admin
			;;
		sqlite)
			./occ maintenance:install \
				--database sqlite \
				--admin-user admin \
				--admin-pass admin
			;;
		*)
			echo "::error ::No database specific initilization in test script. This might be a bug."
			exit 1
			;;
	esac

else
	
	echo 'Copying the app code changes'
	echo 'This might cause trouble when dependencies have changed'
	rsync -a /app/ apps/cookbook/ --exclude /.git --exclude /build --exclude /.github
	
fi

echo 'Installing the cookbook app'

./occ app:enable cookbook

echo 'Starting a temporary web server'
php -S localhost:8080 &
pid=$!

pushd apps/cookbook

echo 'Running the main tests'
make test
echo 'Tests finished'

echo 'Copy code coverage in HTML format'
cp -r coverage $GITHUB_WORKSPACE
cp -r coverage-integration $GITHUB_WORKSPACE
cp coverage.xml $GITHUB_WORKSPACE
cp coverage.integration.xml $GITHUB_WORKSPACE

popd

echo 'Running the code checker'
if ! ./occ app:check-code cookbook; then
	echo '::error ::The code checker rejected the code base. See the logs of the action for further details.'
	exit 1
fi
echo 'Code checker finished'

echo 'Shutting down temporary web server'
kill $pid

echo 'Make data folder readable again'
chmod a+rx data
chmod a+r data/nextcloud.log
