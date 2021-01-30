# Automatic deployment using Github Actions

* TOC
{:toc}

## Step-by-step guide to create a new release
The deployment of new releases is carried out using github actions.
This documentation should serve as a step-by-step basis instructions how to publish a new version.

Anyways these steps **do not cover any topics related to testing verification** of the functionality.
The person creating the release will have to make sure though other means that the version generated is valid.

### Preparations

You should have a local version of the repository at hands.
Check out the `master` branch and make sure it is up to date with the upstream branch.

### Create release branch

It is advised to create a new branch to prepare the release.
Through this text we assume, the release `1.2.4` should be generated while the last version was `1.2.3`.
So you might want to create a branch `release/1.2.4` from master.
```
git checkout -b release/1.2.4 master
```

### Update the changelog

In the release branch you will have to prepare the changelog file.
This means, you have to add a new second level heading to the unreleased changes so far.
You might need to add
```
## 1.2.4 - YYYY-mm-dd
```
as one of the first few lines with `YYYY-mm-dd` the current date.

Commit the changes and push them to github.

### Create pull request

Now, you should create a pull request to merge the changes in the new `release/1.2.4` branch into `stable`.
Do not yet merge the PR.
Here is the last chance to do some testing and verification.

### Let the actions do its magic

Now the critical part comes.
As soon as you merge the PR you set the gears into action and a new release will be published both on github and the NC appstore.

The version number that is going to be generated **depends on the commit message of the merge commit into `stable`**.
So before clicking on *Merge* be sure what you are doing.

By default you will generate a new patch version.
So the third part of our version would be incremented.
If you want to generate a different version type like minor (second part incremented) or major version (first part incremented), you need to make this visible.
You have to put a keyword on a line in the commit message to select a version type other than a patch version.

| Keyword | Version type | Generated version |
|---|---|---|
| no keyword | patch version | 1.2.4 |
| `%MINOR_VERSION%` | minor version | 1.3.0 |
| `%MAJOR_VERSION%` | major version | 2.0.0 |

The automatic deployment action will take over once you push to the `stable` branch.
The following actions will take place after the merge:

1. Some fixed parameters will be pre-filled in the codebase with the version number and a commit is generated on the `stable` branch.
1. A new tag `v1.2.4` is generated to save the current state in history on the `stable` branch.
1. The changes introduced in the commit are merged back into the `master` branch.
1. A github release is published and the compiled files are uploaded as a tarball.
1. A release in the nextcloud appstore is registered and published.

## Implementation details

The most recent version is stored in the files `major`, `minor`, and `patch` in `.github/actions/deploy/`.
These files should contain the major, minor and patch number of the *current* version known.
You should not alter these files manually, the action script will handle these files accordingly.

If you need to put the version string automatically somewhere in the code base, you might want to have a look at `fill-in-data.sh` in `.githib/actions/deploy`.
This file will du the changes to the files that need version-updates.
You should consider committing the changes in `create-version.sh` in the same folder if the file is already under version control to update the `master` branch accordingly.
