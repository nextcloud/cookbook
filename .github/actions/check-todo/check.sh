#!/bin/bash

# Looking for all files
find "$1" -type f -name "*.$2" | while read line
do
	file=$(echo "$line" | sed 's@^\./@@')

	grep -noE '(TODO|ToDo|@todo|XXX|FIXME|FixMe).*' "$line" | while read match
	do
		IFS=: read lineno msg <<< "$match"
		echo "::warning file=$file,line=$lineno::Found $msg"
	done
done
