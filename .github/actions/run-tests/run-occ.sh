#! /bin/sh -e

#set -x

cd /nextcloud
php occ "$@"

exit $?

