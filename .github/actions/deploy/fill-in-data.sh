#! /bin/bash

version="$1"

echo "Updating info.xml"
cat .github/actions/deploy/info.xml.dist | sed "s/%%VERSION%%/$version/g" > appinfo/info.xml
