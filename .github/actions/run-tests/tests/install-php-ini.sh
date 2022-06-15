#!/bin/bash

echo 'Installing php.ini for development'
cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
cat > /usr/local/etc/php/conf.d/memory_limit.ini << EOF
memory_limit = 512M
EOF
