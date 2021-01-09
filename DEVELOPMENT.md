# Development of the cookbook app

We are happily accepting code contributions of different types. Here are some hints on this.

## Development environment

For now, there is no included documentation on how to setup your environment best.

You might however have alook at [this repository](https://github.com/christianlupus/nextcloud-docker-debug).
It describes **one** way to get a running development environment for the app.

## Unit tests and code coverage

Currently there is a github action in place that does automatic unit testing upon pushing to github.
These tests are generating code coverage reports as well.

Firstly, for each such run (see in the actions view or the PR tests) there is the option to download the code coverage as zipped HTML page.
Secondly, the [codecov.io service](https://codecov.io/gh/nextcloud/cookbook) is installed, where more details about the coverage can be found.