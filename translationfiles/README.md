# Translations for the cookbook app

This is a quick instruction how to generate translations for the nextcoud app `cookbook`.


## TL;DR

1. Clone a user fork of the [cookbook app](https://github.com/mrzapp/nextcloud-cookbook)
2. Create new branch and checkout
3. Use the `translationfiles/template/cookbook.pot` template and generate/update the `translationfiles/<lang>/cookbook.po` file
4. Commit and create pull request

## Introduction

The user must understand how the translations are handled in general:
In a first step, all translable text must be extracted from the source code.
Then this template can be copied and modified in order to translate these texts.
However the file format is not suitable for automatic online translations.
Thus a second step must be done in order to generate a better suited structure.

The needed steps will be described in the following sections in detail.

## Steps to generate a new translation

The new translation must be put in the folder `translationfiles/<lang>` and have the name `cookbook.po`.
The `<lang>` is the [language code](http://www.lingoes.net/en/translator/langcode.htm) of the desired translation.

To start a new translation you need first to create a fork of the git repository. 
Click on the fork button of the repo [cookbook](https://github.com/mrzapp/nextcloud-cookbook).
Then in a folder on your machine check out the forked repo:

```
git checkout <SSH-based repo URL> cookbook
```
This will generate a folder cookbook in the current folder.
Enter this new folder.

Then, create a new local branch:

```
git checkout -b <branchname>
```
Replace `<branchname>` with something useful like `translation/de`.

Go into the folder `translationfiles`.
Create a new folder with the corresponding language code (e.g. `de`).
Copy the template file from `template/cookcook.pot` to the file `cookbook.po` (without the `t` at the end) in your newly created folder.

Congratulations!
Now you are done creating a new translation.
Go to the next section to make the translations themselves as if you would simply change an existing translation.

## Steps to modify an existing translation

If not already done, start with the setps from the previous section (Generate a new translation) to checkout and create a local branch.
Then go to the folder `translationfiles` in your repo.
Within it there should be a folder named with the desired language code (e.g. `de`).
Enter this folder.

There should be only one file: `cookbook.po`.
There are multiple ways to modify this file.
It is a plain text file so any text editor should work.
However there are dedicated tools to help modifying the translation files, e.g. like lokalize in KDE or similar.
Do your modifications you want to do.

After you are satisfied with the modifications you can put changed into a git commit.
To do so simply issue

```
git add cookbook.po
git commit
```

You will be asked for a commit message.
Write something useful there.
Finally you can push the changes to your forked repoo by

```
git push -u <remote> <branch>
```

You must replace `<remote>` with the remote you used (or `origin` if you do not know what this means) and `<branch>` by the name of the local branch you created ealier (`translation/de` above).

This will push the changed to your fork of the repo on github.
To notify the developer of the changes, you need to open a pull request.
To do so, git already outputs during the `git push` command execution an URL to help crating suh a pull request.
Open the link in a browser and fill in the fields (sort of comments).
The developer will either merge the chenages into the main development branch or come back to you with further questions.

## Steps to generate the necessary files in order to test the translation

The chenages to the `.po` files will not reflect in the nextcloud app without further modifications.
To see the changes in the app online, you need to update the files in the folder `l10n` of the app.
These files are used by the app to quickly translate any text in the app.

These steps here are just to see the effect of the changes and are not necessary for creating a pull request or helping with translations directly.

You first need to download the translation tool.
Using the commans line this can be done e.g. by

```
wget https://github.com/nextcloud/docker-ci/blob/master/translations/translationtool/translationtool.phar?raw=true -O /tmp/translationtool.phar
```
You can also download the URL in the command in your favorite browser.

Navigate to the root folder of the working copy of your fork on your local machine using a console.
Issue the command

```
php /tmp/translationtool.phar convert-po-files
```
This will update the files in the `l10n` folder.
You can copy these files to your nextcloud installation to test the effect (backup the previous content!).
If you managed to check out the git repo directly in the nextcloud tree, it works instantly.
