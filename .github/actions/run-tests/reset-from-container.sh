#! /bin/bash -e

BACKUP="$1"

echo "Restoring backup with name $BACKUP"

echo "Cloning data files"
rsync --archive --delete --delete-delay "/dumps/$BACKUP/data/" /nextcloud/data/

echo "Restoring DB"
case "$INPUT_DB" in
	mysql)
		mysql -u root -p"$MYSQL_ROOT_PASSWORD" -h mysql < "/dumps/$BACKUP/sql/dump.sql"
		;;
	pgsql)
		echo 'Dropping old data'
		PGPASSWORD="$POSTGRES_PASSWORD" \
		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" -v 'ON_ERROR_STOP=1' <<- EOF || exit 1
			DROP SCHEMA public CASCADE;
			CREATE SCHEMA public;
			GRANT ALL ON SCHEMA public TO $POSTGRES_USER;
			GRANT ALL ON SCHEMA public TO public;
			EOF
		
# 		PGPASSWORD="$POSTGRES_PASSWORD" \
# 		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" <<- EOF
# 			\l
# 			\d
# 			SELECT * FROM oc_preferences WHERE appid='cookbook';
# 			EOF
		
		echo 'Inserting dump data'
		PGPASSWORD="$POSTGRES_PASSWORD" \
		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" -f "/dumps/$BACKUP/sql/dump.sql" -v 'ON_ERROR_STOP=1' || exit 1
		
# 		PGPASSWORD="$POSTGRES_PASSWORD" \
# 		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" <<- EOF
# 			SELECT * FROM oc_preferences WHERE appid='cookbook';
# 			EOF
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
