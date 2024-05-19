# Management of changelogs

* TOC
{:toc}

The handling and creation of a changelog is part of the usual development work.
There are different variants to create sucha a changelog (semi-) automatically.
Instead, we rely on a script that combines a set of snippets and created the changelog files as needed.

This process requres some user interaction in order to work.
There is also a checker on the repository in place that will bail out unless the expected file structue is met.

## Day-to-day usage as a developer

There is a special folder `.changelog/current` in the root folder of the repo.
This folder will have all the snippets for the changelog that are _not yet released_.

Each pull request is obligated to create a file within said folder.
The file of a pull request must begin with the number of the pull request and a dash.
For example, let's assume the imaginary pull request 1234 fixes the output of some JSON export.
The corespoinding snippet would be in the file `.changelog/current/1234-fix-json-output.md`.
The text `fix-json-output` is generic here and only helps with file management as a human.
Please try to be as precise and short as possible.

At the time of writing there are only Markdown files (`*.md`) accepted.
There is the plan to add further file types (JSON, YAML, TOML) in the future.

The developer is responsible for creating an appropriate file in the correct location, filling it with the correct content, and committing/pushing the file.
The check will block a non-administrative merge of the PR unless an appropriate file has been found.

### Format of basic snippet

A snippet can be rather simple.
An example could be

```md
# Fixed

- JSON output for filters adopted to latest API definition
```

The parser will then collect all snippets, combine them according to their names and creats a Changelog file.
The above example woulbe be extended by one line containing the PR number and link as well as the PR creator as author:

```md
...
### Fixed
...
- JSON output for filters adopted to latest API definition
  [#1234](https://github.com/nextcloud/cookbook/pull/1234) @max.musterman
...
```

### Full format snippet in Markdown format

For more advanced cases, more options are there to set.
Here is an example in Markdown:

```md
Author: @dependabot @max-muster

# Added
- New UI eleemnts

# Deprecated
- Doubled elements in Vue tree

# Unknown
- Do fancy stuff
```

This will render something like this:
```md
### Added
...
- New UI eleemnts
  [#1234](https://github.com/nextcloud/cookbook/pull/1234) @dependabot @max-muster
...

### Deprecated
...
- Doubled elements in Vue tree
  [#1234](https://github.com/nextcloud/cookbook/pull/1234) @dependabot @max-muster
...

### Unknown
...
- Do fancy stuff
  [#1234](https://github.com/nextcloud/cookbook/pull/1234) @dependabot @max-muster
...
```

Note that the headings are sorted first the known headings (_Added_, _Changed_, _Fixed_, _Deprecated_, _Removed_, _Documentation_, and _Maintenance_).
After them, the remaining headings come sorted alphabeticlly.

## Technical background

The actual implementation of the scripts cannot be explained here.
Instead a rough sketch of thea how things should work are given.

Generally, the Changelog file consissts of basic blocks:

1. An optional prefix
1. The heading `## [Unreleased]`
1. Then, it comes the _Unreleased_ things:
	1. A heading like `### Added` or similar
	1. The different merged PRs with a one line introduction, a link to the PR, and a reference to the author
1. The already published releases, each one consisting of:
	1. A corresponding heading like `## 1.2.3 - YYYY-MM-dd` consisting of version and date
	1. The actual things in the same manner as the unreleased ones (headings plus list of PRs merged, see № 3.)

The script to build the changelog bases on these building blocks.
The content is stored in the `.changelog` folder in the root of the repo.

### The new changes (`current`)

In the `.changelog` folder there is a folder called `current`.
All merged PRs have an appropriate file in this folder.

Speaking in the building blocks, these represent the unreleased stuff.

The final ordering of the entries in the final Changelog depends on the structure of the git history.
Therefore, to create an ordering, the corresponding merge commits needs to be identified.
Using the topological ordering of `git log` the relative ordering of the PRs can be derived (see `man git-log` section `--topo-order`).

### Fixed releases (`versions`)

Once, a release is made, no changes are allowed to the corresponding changelog anymore.
This is realizedby fixing the corresponding part of the Changelog as individual file.
These version files are located in `.changelog/versions`.
For example, the version `0.11.0` is located in `.changelog/versions/v0.11.0.md`.

These files are already in the parsed format as the final changelog will be.
Thus, no parsing is needed here to create the final result of a certain version in the past.

These files should have the corresponding heading already embedded.
Best is to have two empty lines at the end to get a smooth final markdown changelog file.

### Buidling the complete Changelog file

The complete changelog can now be constructed by concatenation of various files and program outputs.
The prefix lies in a file `.changelog/prefix.md` and can be used as a first part directly.
Then, the unreleased stuff follows.
Here, the heading needs manual intervention and the parsing and data collection can be delegated to a python script located in `.helpers/changelog`.
The script uses pipenv and has some CLI parameters to be used.
It outputs on the stdout the content of the `[Unreleased]` section.
Finally, the different versions need to be appended in reverded versioning order (see `sort -V`).

### Creating of changelogs for relaeses

In order to make the usage as convinient for the dev/admin as possible, there are two scripts `.helpers/changelog/create-changelog-prerelease.sh` and `.helpers/changelog/create-changelog-release.sh`.

**Please be careful:** The latter script will remove files. Please read this section completely before actually calling it.

#### Common options

Both scripts have some options they share.

As the current implementation has to use the GitHub API in order to get some information about the PRs, you could quickly run into rate limiting issues.
To avoid these, the script assumes, you have a personal access token (PAT) created on GitHub.
Put the token in some file (e.g. `.changelog/token`) but **do not commit it**.
You can also put the file outside the repo to be sure, that you do not accidetnially commit the PAT.
The PAT can be provided as file name to the script using the `-t` (or `--token`) option.

The verbosity can be increased by means of `-v`.
This can be given multiple times to get more logs.

The option `--ci` makes the script a bit more restrictive to make sure if actually fails in an GitHub action instead of thowing an unheard/unread warning.

Similarly, the `--pr` option is present which takes a (PR) number as argument.
It instructs the script to ignore any snipet with this PR number during generation of the changelog.
Instead it is apeended at the very ending.
This is needed if a PR is not yet merged but the corresponding snippet is already present (in the GitHub action this is the case).
As the merge commit will not be found, this would otherwise bail out.
Normally, the dev does not need to give this option.

The script then takes the snippets (_not just the folder of the snippets_) in random order as positional arguments.
Typically this is something like `./helpers/changelog/create-changelog-prerelease.sh <other options> .changelog/current/*` in bash.

#### Prereleases

The first script (for prereleases) will carry out the steps as [described above](#buidling-the-complete-changelog-file).

The script expects no additional options and will rewrite the `CHANGELOG.md` file completely.

#### Final releases

**Attention: This script will remove data! Make sure, you have everything committed prior to continuing.**

The second script for releases will however create a new version snippet in `.changelog/versions` from the current state of the `.changelog/current` parts.
After successful creation, the snippets in the current folder are considered obsolete and removed.
A new release has just been prepared.

This script expectes one additional parameter:
It needs the verion to create as first positional argument.
Typically this is something like `./helpers/changelog/create-changelog-release.sh <other options> 0.11.1 .changelog/current/*` in bash.
The date of the release will be set to the date of today.

After having run the script, the `CHANGELOG.md` is updated as well as the file in `.changelog/versions/v0.11.1.md`.
You can have a look if it is correclty built.
If the result has a minor uissue, you might want to edit the version file in `.changelog/versions` directly.

You will have to commit the changes manually as the `.changelog` folder has now some changes in it.
