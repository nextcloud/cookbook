#!/bin/sh -e

set -x

env

cd /tests
echo "Creating test docker image with the following settings"
echo "PHP version: $INPUT_PHPVERSION"

echo docker build -t docker-tests --build-arg PHPVERSION="$INPUT_PHPVERSION" .
echo docker run -e INPUT_DB -v "$HOST_GITHUB_WORKSPACE":"/workdir" docker-tests
