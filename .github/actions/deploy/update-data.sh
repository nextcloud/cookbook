#! /bin/sh

# set -x

deploy_path='.github/actions/deploy'

major=$(cat "$deploy_path/major")
minor=$(cat "$deploy_path/minor")
patch=$(cat "$deploy_path/patch")

version="$major.$minor.$patch"

"$deploy_path/fill-in-data.sh" "$version" "$major" "$minor" "$patch"
