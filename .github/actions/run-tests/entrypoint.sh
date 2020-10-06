#!/bin/sh

env

cd /tests
echo "Creating test docker image with the following settings"
echo "PHP version: $PHP_VERSION"

docker build -t docker-tests --build-arg PHPVERSION="$PHP_VERSION" .
docker run docker-tests
