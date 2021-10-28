# Test scripts

- [Generic steps](#generic-steps)
- [Manual invocation](#manual-invocation-of-the-tests)
- [Typical steps involved to run tests locally](#typical-steps-involved-to-run-tests-locally)
- [Additional configuration using environemnt variables](#additional-configuration-using-environemnt-variables)
- [Implementation details and storage locations](#implementation-details-and-storage-locations)
- [Automatic usage](#automatic-usage)

The scripts in this folder should allow both local (manual) testing of the app as well as automatic continuous integration testing. This documentation should cover both useages and flavours of these scripts.

Please note that the test scripts have changed since the inital version and everything is now controlled using the `run-locally.sh` script.

## Generic steps

In order to allow running the integration tests some integration needs to be prepared. This is done by the script as well as preparing the reuired steps to run the tests themselves in a tightly defined environment (docker container).

The unit tests are running solely with mocking objects so no much integration is needed there.

For the integration tests, a complete nextcloud installation is needed. The scripts provided here will set up such an installation, including a corresponding database. Aditionally, a web server is started to test interaction with foreign servers, e.g. to test importing of recipes from 3rd party websites.

To allow easy setting up of the environment there is an option to save the complete environment (datbase and file structure) and restore during the tests later. This should be faster than installing the nextcloud server for each test.

A complete run will all tests would consist of the following steps:

1. Pull all helper images like mysql, postgres, apache and nginx.
2. Build a set of images to run the tests within.
3. Start the database containers.
4. Install the nextcloud server.
5. Create a `plain` dump for testing of migrations.
6. Enable the cookbook app within the Nextcloud server.
7. Save the environemnt (data files and DB dump).
8. Start of additional containers like Apache and nginx container for integration testing.
9. Run the tests themselves within the docker container.
10. Shutdown all containers no more needed.

There is a script called `run-locally.sh` that handles all these steps plus some housekeeping. It takes command line options to specify which steps to carry out. When in doubt, you can always execute `./run-locally.sh --help` to see an list of the options.

## Manual invocation of the tests

The order of the parameters of the script is not of importance. The order of the steps to be carried out is fixed and chosen such that a typical test run can be carried out quickly.

If you need a special ordering for whatever reason, please call the script multiple times.

### Handling of the docker images

The following options are available to manage the docker images:
```
  --pull                            Force pulling of the latest images from docker hub.
  --create-images                   Force creation of custom docker images locally used for testing
  --create-images-if-needed         Only build those images that are not existing currently
  --push-images                     Push images to docker. Not yet working
```

Typically, one will use the `--create-images` flag which will pull any images and rebuild the existing images if anything has changed.

By default, the image built for testing is a customized version of the `php` image. The customized version is built once per night and can be repulled with `--pull`. If you encounter issues with a dated PHP version or dependencies, you could refetch/rebuild the image locally.

To push images credentials are needed. This is only required to speed up the the build prucess for other users. You will not have to do this unless you know exacly what it does.

### Handling the helper containers

You can ask the script to start the containers and stop them at the termination of the script.
```
  --start-helpers                   Start helper containers (database, http server)
  --shutdown-helpers                Shut down all containers running
```

### Managing the environment

The environemnt is the databse and the file structure. During integration tests these can change and need to be reset using a fixute. These parameters allow to create such a fixture easily.

Creation of an environment means to set up a new nextcloud instance in the existing folder stucture. This might take some time (in the range of 5-10 min) and also some download bandwith as the server source code needs to be downloaded.

To speed things up, fixtures can be saved on the local disc and replayed later during the integration tests if needed. Once a fixture is saved, the corresponding name is reserved. Creating another fixture with the same name is prohibited by default. This behavior can be overridden by an explicit flag `--overwrite-env-dump`.

There are two fixtures involved:
- The `plain` fixture is the state just after successfully install the Nextcloud core. It can be used for testing migrations.
- The `default` fixture (can be renamed with `--env-dump-path`) with the cookbook app just installed.

The name of the fixture is by default `default` but can be changed using `--env-dump-path`. When given multiple times, the last occurence will take precedence.
```
  --setup-environment <BRANCH>      Setup a development environment in current folder. BRANCH dictates the branch of the server to use (e.g. stable20).
  --drop-environment                Reset the development environment and remove any files from it.
  --create-env-dump                 Create a backup from the environment. This allows fast recovery during test setup.
  --restore-env-dump                Restore an environment from a previous backup.
  --drop-env-dump                   Remove a backup from an environment
  --overwrite-env-dump              Allow to overwrite a backup of an environment
  --env-dump-path <PATH>            The name of the environment to save. Multiple environment backups are possible.
  --copy-env-dump <SRC> <DST>       Copy the environment dump in <SRC> to <DST>
  --list-env-dumps                  List all environment dumps stored in the docker volume
```

Using the `--list-env-dumps` and `--copy-env-dump` the user might be able to create a bunch of env dumps representing different versions to test (e.g. different Nextcloud core versions or different PHP versions). Just make sure that the current one to use is called `plain` and `default`.

### Running the tests

The tests are separated into unit tests and integration tests. These can be run separately or together with `--run-tests`.
```
  --run-unit-tests                  Run only the unit tests
  --run-integration-tests           Run only the integration tests
```

To tune the running of the tests, some more flags are present:

```
  --extract-code-coverage           Output the code coverage reports into the folder volumes/coverage/.
  --install-composer-deps           Install composer dependencies
  --build-npm                       Install and build js packages
```

You can create a code coverage report that might be useful to check your tests.
Further you can command composer and/or NPM to update all dependencies before the tests are run in order to simulate a CI run.

### Debugging in test mode

It might make sense to step-debug the tests in case the result is not as intended. There are a few settings prepared to get this running quickly.

```
  --debug                           Enable step debugging during the testing
  --debug-port <PORT>               Select the port on the host machine to attach during debugging sessions using xdebug (default 9000)
  --debug-host <HOST>               Host to connect the debugging session to (default to local docker host)
  --debug-up-error                  Enable the debugger in case of an error (see xdebug's start_upon_error configuration)
  --debug-start-with-request <MODE> Set the starting mode of xdebug to <MODE> (see xdebug's start_with_request configuration)
  --xdebug-log-level <LEVEL>        Set the log level of xdebug to <LEVEL>
  --enable-tracing                  Enable the tracing feature of xdebug
  --trace-format <FORMAT>           Set the trace format to the <FORMAT> (see xdebug's trace_format configuration)
  --enable-profiling                Enable the profiling function of xdebug
```

The flags `--debug`, `--enable-tracing`, and `--enable-profiling` activate the corresponding features for step-debugging, tracing of PHP code and time-profiling the tests. As tracing and profiling tend to generate big files, these should be used with care.

For step-debugging, there needs to be a matich IDE that listens for incoming connections by the PHP debugger [xdebug](https://xdebug.org/). You can configure the port and host using `--debug-port` and `--debug-host`. By default these are set such that a locally running IDE on port 9000 is accessed.

The other flags allow fine-tuning. In case of problems with the IDE , one might consider increasing the log level of xdebug using `--xdebug-log-level` to 10 (debug). There will be a log file written in the folder `volumes/output`.

### Custom options to phpunit and filtering

It is possible to pass arbitrary parameters to the phpunit process. This is especially useful to filter only certain tests. The same parameters are passed to both unit and integration testing. So, if you need different parameters, you should consider calling them separately.

The parameters to the `run-locally.sh` script are separated by `--`. If no `--` is found, the second part is considered empty.The first part of the parameters are the named parameters as described above. The second part are passed 1-to-1 to the PHPUnit process. For possible options see the man page, the internet documentation of PHPUnit or even `./run-locally.sh --run-unit-tests -- --help`.


Using the `--filter` flag of PHPUnit you can pass filters to the phpunit in order to resttrict the test cases if you are actively developing and solving a bug. For example to only run the tests for the tests in `OCA\Cookbook\tests\Unit\Foo` namespace, you could run
```
./run-locally.sh --run-unit-tests -- --filter '^OCA\\Cookbook\\tests\\Unit\\Foo'
```
(Note: The filter string is a regex agains the FQDN of the class. Therefore the `\` must be escaped even inside the `''` quotes. More fancy filters are of course possible.)

## Typical steps involved to run tests locally

First make sure, your user can access the docker daemon. Normally, only root is allowed to access the docker daemon and one has to manually add any additional user to the `docker` group. You should ensure you can run `docker images` as your normal user. Any call to the `run-locally.sh` script should be done as the development user and not as user root as the files generated will belong to the development user then.

Typically one has to setup the test environment once. This can be done by calling `./run-tests --prepare <BRANCH>`. This will set up all docker images, run the helper containers and create a dump as a fixture for later use. The user will have to give a branch name `BRANCH` of the nextcloud server to use.

If you want/need to change the environment for example if a new server version has been released, you will have to drop the environment eventually before you can recreate a new one.

Typically, one will use either `./run-locally.sh --run` or `./run-locally.sh --run-unit-test --extract-code-coverage` to run the tests after the initial setup. This can be run anytime again and the nextcloud installation previously installed will be reused.

Please note that the code coverage runs are archived in the folder `volumes/coverage`. In this folder each test run will be saved with date and time to have some way of backward reference. (Of course these must not be committed to git.) If you generate many coverage reports regularly, you might want to tidy up from time to time.

## Additional configuration using environemnt variables

The `run-locally.sh` script takes a few environemnt variables into account for further configuration.

To select the database to test against in the integrationtests, one can set the variable `INPUT_DB`. Possible values are given in the `--help` message (currently it is `mysql` as default, `pgsql`, and `sqlite`.

The PHP version all scripts are run under can be selected using `PHP_VERSION`. Please note this is just replaced in the docker image version used as a basis for all other container images. So changing the PHP version requires rebuilding of all containers. Also only those versions are allowed for wich docker hub has containers available.

The integration test can run using different HTTP servers. The variable `HTTP_SERVER` selects the HTTP server to use. Currently this is either `apache` or `nginx`.

The vaiables `CI` and `ALLOW_FAILURE` are needed by the continuous integration scripts on github and can be left unset.

## Implementation details and storage locations

This setting uses a set ov docker mount point where different data can be saved. All these mounts are host mounts, so no volumes will be generated in the host that are not removed once the git repository is removed.

All host mounts are relative to the current folder. These are:

- `coverage`: Here the code coverage reports are saved. This folder is mounted in the containers but you should not alter content manually here.
- `cookbook`: A copy of the main code base mounted within the containers. This is mainly used to prevent unintended changes of the test code to your repository.
- `data` The data folder in the nextcloud server is stored here. It is separated from the main nextcloud code codes to allow for simpler backups.
- `dumps`: The script `run-locally.sh` will save the environemnt dumps/fitures here. You should not have to change things here manually.
- `mysql`: The persistent storage for the mysql/mariadb container. Just ignore it.
- `nextcloud`: The nextcloud server code is stored here. Normally you do not need to change anything here.
- `postgres`: The persistent storage for the postgres container. Just ignore it.
- `www`: The storage for a virtual web server to run import tasks against. This folder is the web root of such a web server. You should not put things here directly but your tests should create test files in `/www` within the containers to serve such test files.

## Automatic usage

This set of scripts is well usable using Github Actions to automate the continuous integration (CI) test. It contains a complete action that will run the test suite based on some input variables:

- the PHP version
- the database to be used
- the nextcloud server version
- the HTTP server to use for integration testing

The action just passes the control to the `run-locally.sh` script and calles the same routines as when running locally.

In fact, building and running the container is done by Github Actions. During the building process some dependencies and the corresponding PHP version are installed in the container as for the local build process. Also `npm` is initialised globally.

The main build process involves creating a nextcloud installation, building the main app (fulfill composer and npm dependencies), preparing some database values depending on the selected database and installing the nextcloud main instance. The tests are carried out and the results are kept in the local folder.

If you want to have an interactive look at the code coverage, you might want to go to [codecov.io](https://codecov.io/gh/nextcloud/cookbook). The latest information should be available there.
