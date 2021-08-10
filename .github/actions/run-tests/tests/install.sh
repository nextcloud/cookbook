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

echo 'Running apt-get'
apt-get -qq update
apt-get -qq -y install --no-install-recommends \
	npm make default-mysql-client postgresql-client \
	unzip git libfreetype6-dev libpng-dev libjpeg-dev libzip-dev \
	cmake libpq-dev libsqlite3-dev sudo rsync tini > /dev/null
apt-get clean

echo 'Installing PHP gd'
configure_gd "$1" > /dev/null
docker-php-ext-install -j$(nproc) gd > /dev/null

echo 'Installing PHP zip'
docker-php-ext-configure zip > /dev/null
docker-php-ext-install -j$(nproc) zip > /dev/null

echo 'Installing PHP pdo plus mysql, postgres and sqlite extensions'
docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql pdo_sqlite > /dev/null

echo 'Installing PHP xdebug'
pecl install xdebug > /dev/null
docker-php-ext-enable xdebug > /dev/null

echo 'Installing php.ini for development'
cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
cat > /usr/local/etc/php/conf.d/memory_limit.ini << EOF
memory_limit = 512M
EOF

echo 'Installing PHP extensions done.'

echo 'runner ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers

echo 'Installing NPM globally'
npm install -g --quiet --loglevel warn npm@latest

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

echo 'Installing phpunit PHAR'
wget -O /phpunit https://phar.phpunit.de/phpunit-9.phar
chmod +x /phpunit
