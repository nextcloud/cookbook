#! /bin/bash -e

# set -x

if [ $# -lt 1 ]; then
        echo "Please provide the name of a file to read the commit message from"
        exit 1
fi

deploy_path='.github/actions/deploy'

stable_branch=$(cat "$deploy_path/stable_name")
master_branch=master

major=$(cat "$deploy_path/major")
minor=$(cat "$deploy_path/minor")
patch=$(cat "$deploy_path/patch")

# release
message="$(cat "$1")"

parse_pre_release () {
        echo "$1" | grep -E '%PRE-?RELEASE%' | head -n 1 | sed -E 's@.*%PRE-?RELEASE%([^%]+)%.*@\1@'
}

if echo "$message" | grep '%MAJOR%' > /dev/null ; then
        
        echo 'Creating major version'
        let major=major+1
        minor=0
        patch=0
        
elif echo "$message" | grep '%MINOR%' > /dev/null; then
        
        echo 'Creating minor version'
        let minor=minor+1
        patch=0
        
else
        
        echo 'Creating patch version'
        let patch=patch+1
        
fi

prerelease="$(parse_pre_release "$message")"
version="$major.$minor.$patch"

if [ -n "$prerelease" ]; then
        # We want to build a pre-release
        echo "Not Updating the version files as we are creating a pre-release."
        version="$version-$prerelease"
else
        echo "Updating bumped version files"
        echo $major > "$deploy_path/major"
        echo $minor > "$deploy_path/minor"
        echo $patch > "$deploy_path/patch"
fi

echo "The new version is $version."

echo "Storing new release version in $deploy_path/last_release"
echo "$version" > "$deploy_path/last_release"

"$deploy_path/update-data.sh" "$version" "$major" "$minor" "$patch" "$prerelease"

git add "$deploy_path/major" "$deploy_path/minor" "$deploy_path/patch" "$deploy_path/last_release"

# exit

git config user.name 'Github actions bot'
git config user.email 'bot@noreply.github.com'

git commit -s -m "Bump to version $version"
git tag "v$version"

git checkout $master_branch
git merge --no-ff $stable_branch

git remote add tokenized "https://nextcloud-cookbook-bot:$BOT_TOKEN@github.com/nextcloud/cookbook.git"

git -c "http.https://github.com/.extraheader=" push tokenized $stable_branch $master_branch
git push origin "v$version"

echo "::set-output name=version::$version"
