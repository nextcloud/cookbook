#! /bin/bash -e

# set -x

deploy_path='.github/actions/deploy'

stable_branch=stable
master_branch=master

major=$(cat "$deploy_path/major")
minor=$(cat "$deploy_path/minor")
patch=$(cat "$deploy_path/patch")

# release
message=$(git log HEAD~1...HEAD --max-count=1 --format='%s%n%b')

if echo "$message" | grep '%MAJOR%' > /dev/null ; then
        
        echo 'Creating major version'
        let major=$major+1
        minor=0
        patch=0
        
elif echo "$message" | grep '%MINOR%' > /dev/null; then
        
        echo 'Creating minor version'
        let minor=minor+1
        patch=0
        
else
        
        echo 'Creating patch version'
        let patch=$patch+1
        
fi

echo "Updating bumped version files"
echo $major > "$deploy_path/major"
echo $minor > "$deploy_path/minor"
echo $patch > "$deploy_path/patch"

git add "$deploy_path/major" "$deploy_path/minor" "$deploy_path/patch"

version="$major.$minor.$patch"
echo "New version is $version."

git config user.name 'Github actions bot'
git config user.email 'bot@noreply.github.com'

"$deploy_path/update-data.sh"

git add package.json lib/Controller/MainController.php

git commit -s -m "Bump to version $version"
git tag "v$version"

git checkout $master_branch
git merge --no-ff $stable_branch

git remote add tokenized "https://nextcloud-cookbook-bot:$BOT_TOKEN@github.com/nextcloud/cookbook.git"

git -c "http.https://github.com/.extraheader=" push tokenized $stable_branch
git -c "http.https://github.com/.extraheader=" push tokenized $master_branch
git push origin "v$version"

echo "::set-output name=version::$version"
