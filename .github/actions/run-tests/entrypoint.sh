#!/bin/sh -e

# env

cd /tests
echo "Creating test docker image with the following settings"
echo "PHP version: $INPUT_PHPVERSION"

docker build -t docker-tests --build-arg PHPVERSION="$INPUT_PHPVERSION" .
docker run -e INPUT_DB docker-tests
