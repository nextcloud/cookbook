#! /bin/bash

version="$1"
major="$2"
minor="$3"
patch="$4"

echo "Updating info.xml"
cat .github/actions/deploy/appinfo/info.xml.dist | sed "s/%%VERSION%%/$version/g" > appinfo/info.xml

echo "Updating package.json"
sed "s/\"version\": \\?\"[^\"]*\"/\"version\": \"$version\"/g" -i package.json

echo "Updating version in main controller"
sed "/VERSION_TAG/s@[[].*[]]@[$major, $minor, $patch]@" -i lib/Controller/MainController.php
