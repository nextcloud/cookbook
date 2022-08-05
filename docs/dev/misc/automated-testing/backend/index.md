# Overview over the backend testing implementation

## Contents
1. TOC
{:toc}

## Requirements
The cookbook app serves as a plug-in for the Nextcloud server.
Thus, it uses significant parts of the core classes.
As a result, all tests (be them unit test or integration tests) need to be called in an environment of a Nextcloud server.

Having such a server running, all requirements for the server needs to be satisfied.
That is, there needs to be a database with which the server and thus the app will connect during the tests.
There are multiple types of databases supported by the Nextcloud core, thus the cookbook app needs to mirror that.

For integration and end-to-end testing (including calls through the server), a running instance of the server is needed.
For the integration testing, all components of the server need to be in place.
For the end-to-end testing, additionally, a HTTP server is required to handle the requests.

One feature of the app is downloading of JSON data from foreign sites on the backend.
This needs also testing in various ways.
To be independent from any third party servers, another HTTP server is required as a foreign test instance.

## Usage of docker to provide the requirements
To keep the requirements of the testing infrastructure from bleeding into the developers host system, Docker is used.
This allows to select all relevant versions independently from anything installed on the host machine.

The following docker images are in use by _docker-compose_:

1. A database container for the nextcloud server.
   This is called either `mysql` or `postgres` depending on teh DB used.
1. The `dut` container that will carry out all the tests.
   It is a self-built image that has an entrypoint in place to start the tests as soon as the container is run.
1. During installing it is useful to allow to call OCC commands.
   This is handled in the `occ` image.
   The entrypoint just passes any command line parameter to the `occ` PHP script in the container.
   This container shares its volumes with the `dut` container.
   Thus, using this container it is easily possible to call OCC commands inside the test environment.
1. The more general case is if the user wants to call a generic PHP file.
   This can be useful during installing of the cloud.
   Similar to the `occ` container this will just call the script in the same environment as the `dut` container.
1. The `apache` and `nginx` containers allow to access the server in an E2E fashion.
   They will serve the Nextcloud core as installed and allow to test the output of a real API request.
1. As the `apache` and `nginx` containers only provide a HTTP server but no PHP functionality, the container `fpm` is added.
   It contains a FPM-based installation of PHP that will handle the active parts of the serving.
   The HTTP server will just proxy the requests to FPM.
1. Finally, there is the `www` container.
   It simulates a third party server on the network to download recipes and images from.
   It used an unrelated environment and it is possible to configure the serves files from within the `dut` container.

It is highly suggested to give the development user the rights to start/create/stop/kill/manage docker containers and images.
This can for most systems achieved by adding the user to the `docker` system group.

## Involved docker volumes
The communication between the containers is done either as (HTTP) API calls or by file exchange.
In this section, the used docker volumes are presented shortly.
Additionally, some special volumes are identified and explained in more detail.

All volumes are located under the folder `volumes` within the `.github/actions/run-tests` folder.
This keeps the information together.

The volumes in `volumes/mysql` and `volumes/postgres` contain the **raw database data**.
The user will not need to worry about this.

The main Nextcloud **server installation** is located under `volumes/nextcloud`, while the **data** is located under `volumes/data`.
This separation allows to switch the nextcloud server version without loosing the user files present.
Also, this is a technical requirement by the Nextcloud docker image that is used as a base image to keep the data in a separate volume.
The developer might find the nextcloud server logs here in case there was something logged.
Please note that this volume is shared between all related images, thus a change in an E2E test on `fpm` will be visible in the `dut` container as well

Note the `volumes/cookbook` volume.
It contains a **copy of the cookbook source** code.
Upon starting the tests, the code is synced from the main repository to the volume.
This allows to change the source code during the tests without the risk of unintended side-effects on the main development tree.

An interesting folder is `volumes/www`.
This one can be written from within the `dut` container (mounted as `/www`).
This is the **root of the simulated third party server** `wwww`.
So, any test that wants to test with a third part server mocked, must write the expected result in a file under `/www` to configure the mock server accordingly.
The server is PHP-enabled to allow for e.g. adding custom headers or other special things that would need special handling.

Next, there is the `volumes/coverage` volume.
It is mounted in the `dut` container under `/coverage` and used to **extract the code coverage**, as well as profiling results and call traces, generated by xdebug.
The developer can navigate that folder on his host machine to analyze the results of his tests and evaluate the profiling/tracing results.
There is more information to be found on the [documentation on xdebug in the framework](xdebug).

The folder `volumes/output`, mounted under `/output` in the containers, is the location where **xdebug logs** can be places.
These are mainly required during setup of the development environment:
When step-debugging is not working as expected, in these logs there might be the clue.
Apart from that, the user might use it during debugging to extract data from the containers.

Finally, there is the `volumes/dumps` volume.
This one is mounted under `/dumps`.
This folder is storing **fixtures for the tests**.
The exact structure will not match in this section.
See [the dedicated docs on the fixtures](fixtures) for more details.
The fixtures are generated by the `run-locally.py` script on request.

## See also
- [Debugging the tests](xdebug)
- [Fixtures for automated tests](fixtures)
