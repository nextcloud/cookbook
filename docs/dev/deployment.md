# Automatic deployment using Github Actions

* TOC
{:toc}

## Step-by-step guide to create a new release
The deployment of new releases is carried out using a script and github actions.
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

### Update the changelogs

In the release branch you will have to prepare the changelog file.
This means, you have to add a new second level heading to the unreleased changes so far.
You might need to add
```
## 1.2.4 - YYYY-mm-dd
```
as one of the first few lines with `YYYY-mm-dd` the current date.

Also check if any pending API change is present.
Update the API changelog (in `/docs/dev/api/changelog/*.md`) accordingly.

Commit the changes.
Pushing them to GitHub is neither needed nor adviced.

### Run the script to carry out the release preparations

To create all relevant git structures, there is a script located in `.helpers/release/create-release.sh`.
It will while executed change the various git branches involed (the stable branch, the master branch and the release branch).

**Please note:**
This script will carry out quite some changes.
Although all can be reversed so far, multiple branches need to get unwinded to do so.
So, be sure you know what you are doing and double-check the command line parameters.
This is especially true, if you enable automatical pushes to the remote.

The script provides several CLI parameters that control how the script behaves and what will be caried out.
These scipt parameters are:

| Parameter | Description |
|-----------|-------------|
| `--major` | Create a major version instead of a minor or patch version. |
| `--minor` | Create a minor version instead of a patch version. |
| `--pre-release <suffix>` | Create a pre-release by appending `<suffix>` to the version string appended by a dash. |
| `--dry` | Run the script in dry mode. No changes are carried out. |
| `--remote <name>` | Set the name of the remote to push the branches/tags to. Defaults to `origin` |
| `--push` | If given, the updated branches and tags are pushed automatically to the remote. |

#### Regular releases

By default you will generate a new patch version.
So the third part of our version would be incremented.
If you want to generate a different version type like minor (second part incremented) or major version (first part incremented), you need to provide the corresponding CLI parameter.
The following table shows the effect of the parameters if the last version was `1.2.3`.

| parameters | Version type | Generated version |
|---|---|---|
| _none_ | patch version | 1.2.4 |
| `--minor` | minor version | 1.3.0 |
| `--major` | major version | 2.0.0 |
| `--minor --major` | major version (more precedence) | 2.0.0 |

#### Pre-releases

It is also possible to publish a pre-release like `1.4.2-beta1` or `4.0.2-rc1`.
The user/developer is responsible to provide a valid suffix for the pre-release.
The created pre-release version is **not permanently stored**.

The reasoning of not storing the last pre-release version is that the next regular release (of whatever version level) must not increase the major/minor/patch version multiple times.
Also it might be required to change from a patch to minor or from minor to major level updates:
... > `1.2.3` > `1.2.4-rc1` > `1.3.0-rc1` > `1.3.0` > ... .

A pre-release is created by adding additionally to the parameters as described in the [regular releases section](#regular-releases) the additional parameter `--pre-release`.
The only value if this flag will be used as the suffix of the pre-release.
Assume, you are on version `1.2.3`, the following table explains the resulting release versions:

| Keyword | Generated version |
|---|---|
| `--pre-release rc1` | 1.2.4-rc1 |
| `--minor --pre-release alpha3` | 1.3.0-alpha3 |
| `--pre-release beta.1 --major` | 2.0.0-beta.1 |

#### Next steps

The script will update the stable and corresponding main branch and create a tag (e.g. `v1.2.4`) as well.
Unless automatic pushes are activated via `--push`, the user is reponsible to push the changes to the server.

### Push the version to the appstore

Finally, the release can be published to the app store by pushing the pure tag to the release repository.
The Github actions there will build the app, create a release and finally trigger a new release in the Nextcloud app store.

**Attention:**
This is the final point where you have control over the process.
Once you pushed the tag to the releases repository, it will be published (unless a failure happens).
Undoing the publication needs manual intervention.

## Implementation details

The most recent version is stored in the files `major`, `minor`, and `patch` in `.github/actions/deploy/`.
These files should contain the major, minor and patch number of the *current* version known.
You should not alter these files manually, the action script will handle these files accordingly.

If you need to put the version string automatically somewhere in the code base, you might want to have a look at `fill-in-data.sh` in `.githib/actions/deploy`.
This file will du the changes to the files that need version-updates.
You should consider committing the changes in `create-version.sh` in the same folder if the file is already under version control to update the `master` branch accordingly.
