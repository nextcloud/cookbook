# Test scripts

The scripts in this folder should allow both local (manual) testing of the app as well as automatic continuous integration testing. The main documentation can be found under `/docs`.

Please note that the test scripts have changed since the initial version and everything is now controlled using the `run-locally.py` script.

## Installation

A typical python installation should be sufficient to run the script.

However, for some output functionalities, additional libraries are needed. It is advised to install these in a dedicated virtual environment. These are the steps:

1. Make sure you are in the path of the `run-locally.py` file.
1. Create a virtual environment `venv` by calling `virtualenv venv`.
1. Activate the virtual environment. With Bash that can be done by `. ./venv/bin/activate` (note the space between the two dots).
1. (Optional) Install/update `pip` by issuing `pip install --upgrade pip`.
1. Install the dependencies with `pip install -r requirements.txt`.

This will download and install all configured dependencies of the script.

Please note that only the current console will use the virtual environment. **If you open a new console or close your shell, you need to repeat step 3.**

## Custom options to phpunit and filtering

It is possible to pass arbitrary parameters to the phpunit process. This is especially useful to filter only certain tests. The same parameters are passed to both all types of tests. So, if you need different parameters for the classes, you should consider calling them separately.

The parameters to the `run-locally.py` script are separated by `--`. If no `--` is found, the second part is considered empty.The first part of the parameters are the named parameters as described in the help. The second part are passed 1-to-1 to the phpunit process. For possible options see the man page, the internet documentation of PHPUnit or even `./run-locally.py --run-unit-tests -- --help`.


Using the `--filter` flag of PHPUnit you can pass filters to the phpunit in order to restrict the test cases if you are actively developing and solving a bug. For example to only run the tests for the tests in `OCA\Cookbook\tests\Unit\Foo` namespace, you could run
```
./run-locally.py -qu -- --filter '^OCA\\Cookbook\\tests\\Unit\\Foo'
```
(Note: The filter string is a regex against the FQDN of the class. Therefore the `\` must be escaped even inside the `''` quotes. More fancy filters are of course possible.)

## Typical steps involved to run tests locally

First make sure, your user can access the docker daemon. Normally, only root is allowed to access the docker daemon and one has to manually add any additional user to the `docker` group. You should ensure you can run `docker images` as your normal user. Any call to the `run-locally.py` script should be done as the development user and not as user root as the files generated will belong to the development user then.

Typically one has to setup the test environment once. To do so, one has to build all related docker images with `./run-locally.py -c`. After the images are present, the user can create a fixture with `./run-locally.py --create-fixture <NAME> <BRANCH>` (see the documentation). Finally, the user will activate the fixture by `./run-locally.py --activate-fixture <NAME>`. The commands can be combined, the script will take the action in a sensible order.

If you want/need to change the environment for example if a new server version has been released, you will have to create a new fixture and activate it.

Typically, one will use either `./run-locally.sh --run` or `./run-locally.sh -qux` to run the tests after the initial setup. This can be run anytime again and the nextcloud installation previously installed will be reused.

Please note that the code coverage runs are archived in the folder `volumes/coverage`. In this folder each test run will be saved with date and time to have some way of backward reference. (Of course these must not be committed to git.)

## Automatic usage

This set of scripts is well usable using Github Actions to automate the continuous integration (CI) test. It contains a complete action that will run the test suite based on some CLI parameters (or env variables if required):

- the PHP version
- the database to be used
- the nextcloud server version
- the HTTP server to use for integration testing

The action just passes the control to the `run-locally.py` script and calls the same routines as when running locally.

In fact, building and running the container is done by GitHub Actions. During the building process some dependencies and the corresponding PHP version are installed in the container as for the local build process. Also `npm` is initialized globally.

The main build process involves creating a nextcloud installation, building the main app (fulfill composer and npm dependencies), preparing some database values depending on the selected database and installing the nextcloud main instance. The tests are carried out and the results are kept in the local folder.

If you want to have an interactive look at the code coverage, you might want to go to [codecov.io](https://codecov.io/gh/nextcloud/cookbook). The latest information should be available there soon after all the tests have finished.
