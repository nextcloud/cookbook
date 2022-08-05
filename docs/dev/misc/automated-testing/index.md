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

## Frontend testing

There is not yet any automated tests for the frontend in place.
