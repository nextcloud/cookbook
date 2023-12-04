#! /bin/bash -e

# set -x

cd "$(dirname "$0")/../.."


deploy_path='.github/actions/deploy'

stable_branch=$(cat "$deploy_path/stable_name")
master_branch=$(cat "$deploy_path/trunk_name")
current_branch=$(git branch --show-current)

major=$(cat "$deploy_path/major")
minor=$(cat "$deploy_path/minor")
patch=$(cat "$deploy_path/patch")

use_major=
use_minor=
pre_release=
dryrun=
remote=origin
push=

while [ $# -gt 0 ]
do
	case "$1" in
		--major)
			use_major=Y
			;;
		--minor)
			use_minor=Y
			;;
		--pre-release)
			pre_release="$2"
			shift
			;;
		-n|--dry|--dryrun|--dry-run)
			dryrun=Y
			;;
		--remote)
			remote="$2"
			shift
			;;
		--push)
			push=Y
			;;
		*)
			echo "Cannot parse argument $1"
			exit 1
			;;
	esac
	shift
done

if [ -n "$use_major" ]
then
        echo 'Creating major version'
        let major=major+1
        minor=0
        patch=0
elif [ -n "$use_minor" ]
then
        echo 'Creating minor version'
        let minor=minor+1
        patch=0
else
        echo 'Creating patch version'
        let patch=patch+1
fi

version="$major.$minor.$patch"

if [ -n "$pre_release" ]; then
        echo "Detecting a pre-release version."
		version="$version-$pre_release"
fi

echo "The new version is $version."

# Now, all changes need to be carried out
if [ -n "$dryrun" ]
then
	echo 'Stopping processing as no changes to the git repo should be done.'
	exit 0
fi

count=$(git status --porcelain | wc -l)
if [ $count -gt 0 ]
then
	echo "You have changes in your working copy. Please commit or stash before the changes are overwritten."
	exit 1
fi

# As we are going to change the branch and, thus, potentially overwrite this script file, we pack everything into a function
# The function will be read before and thus executed atomically
# See: https://www.baeldung.com/linux/modify-running-script#1-using-functions
update_git() {
	echo "Checkout stable branch $stable_branch"
	git checkout "$stable_branch"

	echo "Merge release branch $current_branch"
	git merge --no-ff "$current_branch" -m "Merging release branch $current_branch into stable branch $stable_branch."

	if [ -n "$pre_release" ]; then
		# We want to build a pre-release
		echo "Not Updating the version files as we are creating a pre-release."
	else
		echo "Updating bumped version files"
		echo $major > "$deploy_path/major"
		echo $minor > "$deploy_path/minor"
		echo $patch > "$deploy_path/patch"
	fi

	echo "Storing new release version in $deploy_path/last_release"
	echo "$version" > "$deploy_path/last_release"

	"$deploy_path/update-data.sh" "$version" "$major" "$minor" "$patch" "$prerelease"

	git add "$deploy_path/major" "$deploy_path/minor" "$deploy_path/patch" "$deploy_path/last_release"
	git commit -s -m "Bump to version $version"

	tag_name="v$version"
	echo "Creating release tag $tag_name"
	git tag "$tag_name"

	echo "Forwarding main branch $master_branch to be on tie with stable branch $stable_branch"
	git checkout $master_branch
	git merge --no-ff $stable_branch -m "Updating main branch $master_branch with latest stable version information from $stable_branch" --no-verify

	if [ -n "$push" ]
	then
		echo "Pushing data to remote server called $origin"
		echo git push "$origin" "$stable_branch" "$master_branch" "$tag_name"
	else
		echo "Not pushing to server, please push manually"
	fi
}

update_git
