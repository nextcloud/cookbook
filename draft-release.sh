#!/bin/bash

echo ""
echo "Creating archive..."
echo ""

ln -s . cookbook
tar -czvf cookbook.tar.gz cookbook/appinfo cookbook/composer.json cookbook/COPYING cookbook/css cookbook/img cookbook/js cookbook/lib cookbook/Makefile cookbook/phpunit.integration.xml cookbook/phpunit.xml cookbook/README.md cookbook/templates cookbook/tests cookbook/l10n
rm cookbook

echo ""
echo "Creating signing key..."
echo ""

openssl dgst -sha512 -sign ~/.config/nextcloud/certificates/cookbook.key cookbook.tar.gz | openssl base64

echo ""
echo "Done!"
echo ""
