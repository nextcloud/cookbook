# Test scripts

- [Manual usage](#manual-usage)
- [Automatic usage](#automatic-usage)

The scripts in this folder should allow both local (manual) testing of the app as well as automatic continuous integration testing. This documentation should cover both useages and flavours of these scripts .

## Manual usage

For Manuel usage a containerized environment is used. Here `docker` and `docker-compose` are used in order to use exactly same environment for the tests compared with the continuous integration environment.

### Configuration

The first step to do manual testing is to set up the doctor-compose configuration. This is necessary because the configuration is depending on the developer's machine and desires.

1. The build output should be owned by the development user when running Linux. That way, all files can be access without right issues.
2. The PHP version to be testet agains must be set accordingly. There is a default but the developmer might choose to use a different version.

Just tweak the `docker-compose.yml` file according to your needs.

Do not fear if you are not fluent with docker. There is not much to be done here. The sections `mysql` and `postgres` should be left as they are. Just go to `dut` and adjust the build args. Set the desired PHP version and your main user's uid there.

If you are not developing on Linux, you can keep the defaults.

### Folder generation

The nextcloud installation needs to be placed somewhere. Also, the build result must be saved somewhere. To do so, a dedicated folder needs to be generated in this folder (as a sibling to the `docker-compose.yml` file). Call it `workdir`. Under Linux make it owned by your development user.

The folder will be populated later during the build. There will be a few 100MB needed for the installation of a nextcloud instance.

### Building of the image

In order to run the tests, a docker image accoring to your settings needs to be generated. This is done by calling
```
docker-compose build dut
```

This process will take some time as a few dependencies need to be compiled into the image. Just wait for it to succeed.

**Note:** When you want/need to change the test routines in the docker image, you need to rebuild the image as shown in the command above. This is only necessary if you want to change the build process, so you will most probably know what you are doing anyways.

### Run a complete test

The whole testing process is running completely automated. Just go to the corresponding folder where the `docker-compose.yml` file relies. There you can just call the `run-local.sh` script. It will do the following:

- Fire up a set of databases
- Remove any previous test installation
- Cloned the nextcloud server from github including submodules in the folder created above
- Drop all tables from these just created databases (that is reset the dabase to start from scratch)
- Install the nextcloud test instance (create databases and folder structure)
- Finally: Run the device under test (dut).

Note that no data is preserved during such a run. If you change anything it will be overwritten.

### Quicker run of unit tests

To avoid the overhead of removing and reinstalling the nextcloud in each iteration, there is the option to run the unit tests only.

Obviously, this can only work if the rest of the system is prepared accordingly. So you **must** hav called the `run-locally.sh` file at least once to create a basic installation.

After that, by calling
```
docker-compose run --rm dut --test-only
```
you can run only the tests.

Here the database is not affected and the installation is not tested as well. If some strange behaior is observed, a complete run might help.

**Note:** Before publishing large changes a complete build is highly suggested.

### Test coverage

The test will generate code coverage reports for the developer. These can be found in plain HTML format under `workdir/nextcloud/apps/cookbook`.

## Automatic usage

This set of scripts is well usable using Github Actions to automate the continuous integration (CI) test. It contains a complete action that will run the test suite based on some input variables:

- the PHP version
- the database to be used

During the run on the action creates a docker network and the corresponding databases. After the run, the containers are shut down.

In fact, building and running the container is done by Github Actions. During the building process some dependencies and the corresponding PHP version are installed in the container as for the local build process. Also `npm` is initialised globally.

The main build process involves creating a nextcloud installation, building the main app (fulfill composer and npm dependencies), preparing some database values depending on the selected database and installing the nextcloud main instance. The tests are carried out and the results are kept in the local folder.

The application is installed in the folder `nextcloud/apps/cookbook`. There will also reside a set of build artefacts, that are generated during the test run.

If you want to have an interactive look at the code coverage, you might want to go to [codecov.io](https://codecov.io/gh/nextcloud/cookbook). The latest information should be available there.
