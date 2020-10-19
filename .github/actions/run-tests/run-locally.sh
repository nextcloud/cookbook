#!/bin/bash

set -x

cd $(dirname "$0")

docker-compose up -d mysql postgres

mkdir -p workdir

cd workdir

rm -rf nextcloud

git clone --depth=1 --branch stable20 https://github.com/nextcloud/server nextcloud

# git clone --depth=1 https://github.com/nextcloud/cookbook nextcloud/custom_apps/cookbook

cd ..

echo Dropping databases

function call_mysql() {
	docker-compose exec -T mysql mysql -u tester -ptester_pass nc_test
}
function call_postgres() {
	docker-compose exec -T postgres psql -t nc_test tester
}

echo "SHOW TABLES;" | call_mysql | tail -n +2 | sed 's@.*@DROP TABLE \0;@' | call_mysql
echo "SELECT tablename FROM pg_tables WHERE schemaname = 'public';" | call_postgres | head -n -1 | sed 's@.*@DROP TABLE \0;@' | call_postgres

docker-compose run dut

