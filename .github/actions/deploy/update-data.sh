#! /bin/sh

# set -x

if [ "$1" = "--from-files" ]; then
	deploy_path='.github/actions/deploy'

	major=$(cat "$deploy_path/major")
	minor=$(cat "$deploy_path/minor")
	patch=$(cat "$deploy_path/patch")
	suffix=''
	
	version="$major.$minor.$patch"

elif [ $# -eq 5 ]; then
	version="$1"
	major="$2"
	minor="$3"
	patch="$4"
	suffix="$5"
else
	echo "Unsupported command line parameters. Please provide either --from-files or 5 parameters for version, major, minor, patch and suffix."
	exit 1
fi

echo "Updating info.xml"
cat .github/actions/deploy/appinfo/info.xml.dist | sed "s/%%VERSION%%/$version/g" > appinfo/info.xml
git add appinfo/info.xml

echo "Updating package.json"
sed "s/\"version\": \\?\"[^\"]*\"/\"version\": \"$version\"/g" -i package.json
git add package.json

echo "Updating version in main controller"
version_arr="$major, $minor, $patch"
if [ -n "$suffix" ]; then
	version_arr="$version_arr, '-$suffix'"
fi
sed "/VERSION_TAG/s@[[].*[]]@[$version_arr]@" -i lib/Controller/MainController.php
git add lib/Controller/MainController.php
