#!/bin/sh -e

set -x

# env

cd /tests
echo "Creating test docker image with the following settings"
echo "PHP version: $INPUT_PHPVERSION"

docker build -t docker-tests --build-arg PHPVERSION="$INPUT_PHPVERSION" .
docker run -e INPUT_DB -v $GITHUB_WORKSPACE:/workdir docker-tests
