#!/bin/bash -e

set -x

php --version

echo "Preparing the build system"

# Prepare the system
npm install -g npm@latest

cd nextcloud

echo "Build the app"
pushd custom_apps/cookbook
make
popd

echo "Prepare database"

function call_mysql()
{
	mysql -u tester -h mysql -ptester_pass "$@"
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
	*)
		echo "::warning ::No database specific initilization in test script. This might be a bug."
		;;
esac

echo "Initialize nextcloud instance"
mkdir data

case "$INPUT_DB" in
	mysql)
		./occ maintenance:install --database-name nc_test --database-user tester --admin-user admin --admin-pass admin --database mysql --database-pass 'tester_pass'
		;;
	*)
		echo "::error ::No database specific initilization in test script. This might be a bug."
		exit 1
		;;
esac

echo 'Installing the cookbook app'

./occ app:enable cookbook

echo 'Starting a temporary web server'
php -S localhost:8080 &
pid=$!

cd custom_apps/cookbook

make test

kill $pid

###############
echo
ls -la
echo

echo "Environment"
env
