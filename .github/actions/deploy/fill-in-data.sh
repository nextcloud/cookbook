#! /bin/bash

version="$1"

echo "Updating info.xml"
cat .github/actions/deploy/appinfo/info.xml.dist | sed "s/%%VERSION%%/$version/g" > appinfo/info.xml

echo "Updating package.json"
sed "s/\"version\": \\?\"[^\"]*\"/\"version\": \"$version\"/g" -i package.json
