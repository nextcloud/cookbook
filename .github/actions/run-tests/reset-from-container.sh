#! /bin/sh -e

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
		PGPASSWORD="$POSTGRES_PASSWORD" \
		psql -d "$POSTGRES_DB" -h postgres -U "$POSTGRES_USER" < "/dumps/$BACKUP/sql/dump.sql"
		;;
	sqlite)
		echo "Doing nothing as it was already restored during data restoration."
		;;
	*)
		echo "Unknown database name found. this needs to be implemented or is a bug."
		exit 1
		;;
esac

# exit 1
