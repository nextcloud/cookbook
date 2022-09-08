#!/bin/bash

dir=$(dirname "$0")
TOKEN=$(cat "$dir/token.file")

curl -H "Authorization: Token $TOKEN" "$@"
