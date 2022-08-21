#! /bin/sh -e

#set -x

cd /var/www/html
php occ "$@"

exit $?

