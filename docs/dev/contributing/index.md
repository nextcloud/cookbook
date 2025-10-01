# Contributing with the project

* TOC
{:toc}

This page should help any willing contributors to start working.
If you need additional information or find any missing parts, feel free to contact us at the [#nextcloud-cookbook room on matrix.org](https://matrix.to/#/#nextcloud-cookbook:matrix.org).

## Checking the JSON of a URL
It makes sense from time to time to look at the JSON of a website directly.
This can be done by using the `getld` go command line program.

To install it, just execute
```
go install github.com/daetal-us/getld@latest
```
You can then download and extract JSON data from a website.
Ideally, you pipe it through `jq` to get a nice formatting:
```
~/go/bin/getld URL_IN_QUESTION | jq
```

## Coding
The first step, when you want to help with coding on the app, will be to setup your development environment.
We prepared a [page on the setup](setup) to help you get started with the technical requirements.
See also the page on [code coverage](code_coverage).
Please also note that the [changelog process](../changelog) needs some addressing as well.

## Translating
Feel free to have a look at the [transifex page on the cookbook app](https://app.transifex.com/nextcloud/nextcloud/cookbook/).
Any translations done there will be synchronized on a nightly base.

## Documentation
We also need helpers in writing documentation for various levels and user groups.
If you think you can help by writing appropriate documentation or tutorials, feel free to step forward.
