#!/bin/bash -e

set -x

php --version

echo "Preparing the build system"

# Prepare the system
npm install -g npm@latest

# Build the system

mount

pwd
echo
ls -la
echo

echo "Environment"
env
