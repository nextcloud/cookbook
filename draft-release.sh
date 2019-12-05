#!/bin/bash

echo ""
echo "Compiling translations..."
echo ""

wget https://github.com/nextcloud/docker-ci/blob/master/translations/translationtool/translationtool.phar?raw=true -O translationtool.phar 
mkdir -p l10n
php translationtool.phar convert-po-files
rm translationtool.phar

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
