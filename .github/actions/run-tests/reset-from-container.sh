#! /bin/bash -e

SUBFIXTURE="$1"

echo "Restoring sub-fixture $SUBFIXTURE"

SF_DIR="/dumps/current/$SUBFIXTURE"

is_file_dump () {
	test -f "$SF_DIR/sql/dump.sql"
}

restore_mysql_dump () {
	echo "Dropping old data from the database"
	mysql -u root -p"$MYSQL_ROOT_PASSWORD" -h mysql <<- EOF | tail -n +2 > /tmp/mysql_tables
		SHOW TABLES;
		EOF
	cat /tmp/mysql_tables | sed 's@.*@DROP TABE \0;@' | mysql -u root -p"$MYSQL_ROOT_PASSWORD" -h mysql

	echo "Restoring MySQL from single file dump"
	mysql -u root -p"$MYSQL_ROOT_PASSWORD" -h mysql < "$SF_DIR/sql/dump.sql"
}

restore_postgres_dump () {
	echo "Restoring PostgreSQL from single file dump"
	echo 'Dropping old data'
	PGPASSWORD="$POSTGRES_PASSWORD" \
	psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" -v 'ON_ERROR_STOP=1' <<- EOF || exit 1
		DROP SCHEMA public CASCADE;
		CREATE SCHEMA public;
		GRANT ALL ON SCHEMA public TO $POSTGRES_USER;
		ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO PUBLIC;
		EOF
	
# 		PGPASSWORD="$POSTGRES_PASSWORD" \
# 		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" <<- EOF
# 			\l
# 			\d
# 			SELECT * FROM oc_preferences WHERE appid='cookbook';
# 			EOF
	
	echo 'Inserting dump data'
	PGPASSWORD="$POSTGRES_PASSWORD" \
	psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" -f "$SF_DIR/sql/dump.sql" -v 'ON_ERROR_STOP=1' || exit 1
	
# 		PGPASSWORD="$POSTGRES_PASSWORD" \
# 		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" <<- EOF
# 			SELECT * FROM oc_preferences WHERE appid='cookbook';
# 			EOF
}

get_container_name() {
	echo "cookbook_unittesting_${1}_1"
}

kill_container() {
	local container_name="`get_container_name "$1"`"
	echo "Killing container $container_name"
	docker --log-level error kill "$container_name"
}

start_container() {
	local container_name="`get_container_name "$1"`"
	echo "Starting container $container_name"
	docker --log-level error start "$container_name"
}

restore_db_synced_data() {
	local db_path="/db/${1}"
	echo "Restoring synced DB data from $SF_DIR/sql to $db_path"
	sudo rsync -a --delete --delete-delay "$SF_DIR/sql/" "$db_path"
}

test_mysql_is_running() {
	state=$(docker inspect --format '{{.State.Health.Status}}' "$(get_container_name mysql)")
	test "$state" = "healthy"
}

test_postgres_is_running() {
	PGPASSWORD="$POSTGRES_PASSWORD" \
	psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" -v 'ON_ERROR_STOP=1' <<- EOF
	\q
	EOF
}

wait_for_container() {
	container_test="$1"
	i=0
	while ! "$container_test"
	do
		sleep 0.1
		let i=$i+100
		if [ $i -gt 20000 ]; then
			exit 1
		fi
	done
}

restore_mysql_sync () {
	echo "Restoring MySQL from synced dump"
	kill_container mysql
	restore_db_synced_data mysql
	start_container mysql
	wait_for_container test_mysql_is_running
}

restore_postgres_sync () {
	echo "Restoring PostgreSQL from synced dump"
	kill_container postgres
	restore_db_synced_data postgres
	start_container postgres
	wait_for_container test_postgres_is_running
}

# exec >> /output/reset.log 2>&1

echo "Cloning data files"
rsync --archive --delete --delete-delay "$SF_DIR/data/" /var/www/html/data/

echo "Restoring DB"
case "$INPUT_DB" in
	mysql)
		if is_file_dump ; then
			restore_mysql_dump
		else
			restore_mysql_sync
		fi
		;;
	pgsql)
		if is_file_dump; then
			restore_postgres_dump
		else
			restore_postgres_sync
		fi
		;;
	sqlite)
		echo "Doing nothing as it was already restored during data restoration."
		;;
	*)
		echo "Unknown database name found ($INPUT_DB). this needs to be implemented or is a bug."
		exit 1
		;;
esac

# exit 1
