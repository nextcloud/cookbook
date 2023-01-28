# Automated testing

There are automated test frameworks in place in order to make regressions during development less likely.
This section should describe the related tools and also provide an in-depth view for future enhancements.

## Backend testing

The backend has an automated testing environment prepared.
It is located under `.github/actions/run-tests`.
The same code can be used for both automated testing on the development machines as well as on the GitHub actions in place.

In the folder `.github/actions/run-test` there is the python script `run-locally.py` present.
This script serves as a user interface to allow the tests to be carried out on demand.
For a detailed description of the options, please see `./run-locally.py --help`.
This will provide you with a list of options the script understands.

So allow any future enhancements and bug tracking, there is some sort of documentation in place.
The [overview of the backend testing framework](backend/) should give a general idea of the involved steps.

### Quick start

These instructions will help you quickly get set up for testing using the default options.
If you would like to learn more or use non-default options, you may read [Overview over the backend testing implementation](./backend/index.md).

1. Install [Docker](https://www.docker.com/) (e.g. `sudo apt install docker`). Nextcloud will be installed in Docker for testing.
1. Install [PHP](https://www.php.net/) and some extensions: `sudo apt install php php-zip php-xml`
1. Install [Composer](https://getcomposer.org/), the PHP package manager (e.g. `sudo apt install composer`)
1. Although this app is written in PHP and JavaScript, the test helpers are written in Python. Please [install Python 3](https://www.python.org/downloads/).
1. Install PHP dependencies: `composer install`
1. Open testing directory: `cd .github/actions/run-tests/`.
1. Create a Python virtual environment: `python -m venv venv`.
1. Activate the virtual environment: `source venv/bin/activate`.
1. Install Python dependencies: `pip install -r ./requirements.txt`.
1. Pull required Docker images: `./run-locally.py --pull`.
1. Create a testing fixture: `./run-locally.py --create-fixture stable25 stable25`.
1. Run the tests: `./run-locally.py --run-default-tests`.

## Frontend testing

There is not yet any automated tests for the frontend in place.
