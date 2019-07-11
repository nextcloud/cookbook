#!/bin/bash

ln -s . cookbook
tar -czvf cookbook.tar.gz cookbook/appinfo cookbook/composer.json cookbook/COPYING cookbook/css cookbook/img cookbook/js cookbook/lib cookbook/Makefile cookbook/phpunit.integration.xml cookbook/phpunit.xml cookbook/README.md cookbook/templates cookbook/tests
rm cookbook

openssl dgst -sha512 -sign ~/.nextcloud/certificates/cookbook.key cookbook.tar.gz | openssl base64
