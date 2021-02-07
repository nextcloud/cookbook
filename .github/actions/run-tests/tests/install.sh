#!/bin/bash -e

echo "Install called with parameters '$@'"

set -x

function configure_gd_normal ()
{
	docker-php-ext-configure gd --with-freetype --with-jpeg
	return $?
}

function configure_gd_without ()
{
	docker-php-ext-configure gd
	return $?
}

function configure_gd()
{
	if [ "$1" = "7.2" -o "$1" = "7.3" ]; then
		configure_gd_without
		return $?
	else
		configure_gd_normal
		return $?
	fi
}


apt-get update
apt-get install -y --no-install-recommends \
	npm make default-mysql-client postgresql-client \
	unzip git libfreetype6-dev libpng-dev libjpeg-dev libzip-dev \
	cmake libpq-dev libsqlite3-dev sudo rsync
apt-get clean

configure_gd "$1"
docker-php-ext-install -j$(nproc) gd

docker-php-ext-configure zip
docker-php-ext-install -j$(nproc) zip

docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql pdo_sqlite

pecl install xdebug
docker-php-ext-enable xdebug

echo 'runner ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers

npm install -g npm@latest

# Install composer
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    exit 1
fi

php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
