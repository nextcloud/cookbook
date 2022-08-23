#! /bin/sh

# set -x

deploy_path='.github/actions/deploy'

major=$(cat "$deploy_path/major")
minor=$(cat "$deploy_path/minor")
patch=$(cat "$deploy_path/patch")
suffix=$(cat "$deploy_path/suffix")

version="$major.$minor.$patch"

if [ -n "$suffix" ]; then
	version="$version-$suffix"
fi

fill_in_data () {
	echo "Updating info.xml"
	cat .github/actions/deploy/appinfo/info.xml.dist | sed "s/%%VERSION%%/$version/g" > appinfo/info.xml

	echo "Updating package.json"
	sed "s/\"version\": \\?\"[^\"]*\"/\"version\": \"$version\"/g" -i package.json

	echo "Updating version in main controller"
	local version_arr="$major, $minor, $patch"
	if [ -n "$suffix" ]; then
		version_arr="$version_arr, -$suffix"
	fi
	sed "/VERSION_TAG/s@[[].*[]]@[$version_arr]@" -i lib/Controller/MainController.php
}

fill_in_data
