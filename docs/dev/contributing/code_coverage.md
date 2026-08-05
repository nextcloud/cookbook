<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

## Unit tests and code coverage

Currently there is a github action in place that does automatic unit testing upon pushing to github.
These tests are generating code coverage reports as well.

Firstly, for each such run (see in the actions view or the PR tests) there is the option to download the code coverage as zipped HTML page.
Secondly, the [codecov.io service](https://codecov.io/gh/nextcloud/cookbook) is installed, where more details about the coverage can be found.
