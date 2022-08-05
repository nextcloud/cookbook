# Fixtures for automated tests

- TOC
{:toc}

## Required fixtures
The tests on the backend require some environment to run in.
This environment is defined by
- the Nextcloud server version
- the type of the used database

Additionally, there are some more settings that might or might not affect the tests.
These will not directly influence the fixtures but might have side-effects or trigger different execution paths in the tests.
- the PHP version in use
- the HTTP server type (apache, nginx)

## Saved fixtures
The installation of a Nextcloud server takes a significant amount of time.
Thus, it is not feasible to prepare such a fixture for all tests individually.
Instead, the fixtures will be pre-generated and stored in a dedicated structure.

This will allow the restoring of the state at the beginning of all tests with minimal time overhead.
The management of the fixtures is done by the `run-locally.py` script that will handle the complete preparation of a fixture.
The user (the person developing and wanting to run the tests) needs to prepare one or multiple fixtures before any tests can be run.
Once a fixture has been successfully build, it can be reused any time later.

## Storage location of fixtures
All fixtures are stored in the folder `volumes/dumps/fixtures`.
Inside this folder, each folder represents a single fixture.

Additionally, there is a folder `current` in `volumes/dumps` that represent the currently used dump.
See section [about the current fixture](#the-current-environment) below for more information.

Finally, there is a `version.json` file.
It describes the version of the file structure involved.
See the section below on the [versioning of the file structure](#the-version-of-the-dumps).

An example of the `volumes/dumps` folder (aborted after the 3rd recursion level) can be seen here:
```
volumes/dumps
├── current
│   ├── config.json
│   ├── main
│   │   ├── data
│   │   ├── nextcloud
│   │   └── sql
│   └── plain
│       ├── data
│       ├── nextcloud
│       └── sql
├── fixtures
│   ├── Fixture
│   │   ├── config.json
│   │   ├── main
│   │   └── plain
│   └── Other_fixture
│       ├── config.json
│       ├── main
│       └── plain
└── version.json
```

## Content of a single fixture

### Sub-fixtures
The fixture needs to allow simulating multiple states during the installation process.

For any tests related to the install/update routines (located in `tests/Migration`), it is necessary that the Nextcloud core is completely installed.
The cookbook app should on the other hand *not* be installed.
Installing the preceding migration can then be triggered by the test code.
Then the migration under test can be elaborated in a detailed way.

For all other tests, a successful installation of both the Nextcloud server core as well as the cookbook app is required.
To prevent the tests be lengthy due to the need of installing the app on every test run, there is another sub-fixture introduced that represents a vanilla installed cookbook app.

The two sub-fixtures are called `plain` (just the NC core) and `main` (fully runnable cookbook app).

### Content of a sub-fixture

Each sub-fixture consists of multiple parts:
- the Nextcloud server core files
- the user data of the test user
- the content of the database

These parts are stored separately in the folders `nextcloud`, `data`, and `sql`, respectively.
These folders reside in a common folder that represents the sub-fixture.

The files of the server and of the user are synchronized using `rsync` to and from the working instance.
The database can be stored in different ways:
- Dump of the content as one large SQL file
- Clone of the underlying file system of the database system

The exact type of database data is not fixed here by intention.

### Configuration of fixture
Each fixture has a config file that contains all relevant information of that fixture.
The configuration is written in JSON format in a file `config.json` in the fixtures folder

### Example of a fixture folder

Here comes an example folder structure to represent a complete fixture called `Fixture`.
```
volumes/dumps/fixtures
└── Fixture
    ├── config.json
    ├── main
    │   ├── data
    │   │   ├── admin
    │   │   ├── nextcloud.log
    │   │   └── ...
    │   ├── nextcloud
    │   │   ├── 3rdparty
    │   │   ├── apps
    │   │   ├── index.php
    │   │   └── ...
    │   └── sql
    │       └── ...
    └── plain
        ├── data
        │   ├── admin
        │   ├── nextcloud.log
        │   └── ...
        ├── nextcloud
        │   ├── 3rdparty
        │   ├── apps
        │   ├── index.php
        │   └── ...
        └── sql
            └── ...
```

This folder `Fixture` would be present in `volumes/dumps/fixtures`.

## Configuration of a fixture
The configuration of a fixture is a JSON file in the fixture folder.
Here is an example of such a config file:

```
{
  "finished": false,
  "db": "mysql",
  "branch": "stable25",
  "php_version": "8.1",
  "description": "Default development environment",
  "version": 1,
  "sql_type": "dump"
}
```

In teh following sections, the fields should be described further.

### `finished`
This is a boolean flag if the creation of the fixture has been finished.

Upon creating of the fixture, the first step is to define the version in `config.json` file (see [version below](#version)).
The process of creating a fixture might be interrupted or fail for different reasons.
Only, if the fixture is complete successfully, the config should be updated and `finished` should be set to `true`.

### `db`
The name of the database in use.

This can be `mysql`, `pgsql`, and `sqlite`.
It allows to call code selectively depending on the use DB to trigger e.g. a reset.

### `branch`
The name of the branch of thew Nextcloud server used as the base environment.

During creation of a fixture, a certain branch of the main server is checked out.
This branch defines against which version of the server the app ist tested against.
This entry is mainly informational but might get handy for selective code testing.

### `php_version`
This describes the PHP version used for testing.

This informational flag describes the PHP version the main images are derived from.
It should not taken into account in the tests as this would create brittle tests most probably.

### `description`
This is a string that describes the fixture in human-readable text.
It exists only for informational purposes.

### `version`
The version should prevent accidental usage of wrong data/fixtures if the code base changes and still old fixtures are present on the developers machine.
Then, either a migration can be done or the user can be warned at least.

### `sql_type`
This string defines the type of the SQL dump done in all sub-fixtures.

The valid values are:
- `dump`: A SQL dump is generated that resides in a single SQL file
- `sync`: A filesystem-based clone of the DB's files are stored
- `none`: No DB  dump handling is necessary, typically SQLite as the DB is restored automatically

## The _current_ environment
In order to simplify the development process, there is the _current_ environment.
It is located in the folder `volumes/dumps/current`.

It is used by the tests in the `dut` container as the only source of (sub-) fixtures.
This simplifies the testing code.

Also the user is not required to keep track of the currently used and installed fixture as was with previous version.
After selecting a single environment fixture as the current one, any succeeding test run uses this configuration.
There is no need to pass environment variables or a dozen options for each run.

The current environment can be a hard copy of a real fixture in `volumes/dumps/fixtures`.
Alternatively, it can also be a symlink to such a real fixture.
Note however, that the symlink **must be a relative one**:
It must work in the `dut` container as well and thus absolute symlinks will break.

## The version of the dumps
There is a file `version.json` in the main folder `volumes/dumps`.
It is a simple JSON file that describes the version of the dumps file structure.
An example would be:
```
{
  "version": "1"
}
```

The current version of the documentation is `1`.

If the file is not present or the version is too low, a migration can be done.
If the version is too high, any progress should be stopped to prevent data conflicts.
