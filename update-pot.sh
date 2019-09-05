#!/bin/bash

echo ""
echo "Fetching translation helpers..."
echo ""

test -f translationtool.phar || wget https://github.com/nextcloud/docker-ci/blob/master/translations/translationtool/translationtool.phar?raw=true -O translationtool.phar 

echo ""
echo "Generating/updating POT file..."
echo ""

php translationtool.phar create-pot-files

echo ""
echo "Updating any existing translation files..."
echo ""

temp=`mktemp`

find translationfiles -maxdepth 1 -mindepth 1 -type d ! -name templates | while read f
do
	echo "Updating $f"
	cat "$f/cookbook.po" > $temp
	msgcat --use-first -o "$f/cookbook.po"  "$temp" translationfiles/templates/cookbook.pot
done

rm "$temp"

rm translationtool.phar

