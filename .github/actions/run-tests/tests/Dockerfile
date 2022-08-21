
ARG PHPVERSION
FROM php:${PHPVERSION}-fpm

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

ARG PHPVERSION

RUN apt-get -qq update && \
	apt-get -qq -y install --no-install-recommends \
	npm make default-mysql-client postgresql-client \
	unzip git libfreetype6-dev libpng-dev libjpeg-dev libzip-dev \
	cmake libpq-dev libsqlite3-dev sudo rsync tini wget docker.io > /dev/null && \
	apt-get clean

COPY install-php-ini.sh /tmp/install-php-ini.sh
RUN /tmp/install-php-ini.sh

COPY install-sudoers.sh /tmp/install-sudoers.sh
RUN /tmp/install-sudoers.sh

COPY install-gd.sh /tmp/install-gd.sh
RUN /tmp/install-gd.sh ${PHPVERSION}

RUN docker-php-ext-configure zip > /dev/null && \
	docker-php-ext-install -j$(nproc) zip > /dev/null && \
	docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql pdo_sqlite > /dev/null

RUN pecl update-channels && \
	pecl install xdebug >/dev/null && \
	docker-php-ext-enable xdebug > /dev/null

RUN npm install -g --quiet --loglevel warn npm@latest

COPY install-composer.sh /tmp/install-composer.sh
RUN /tmp/install-composer.sh

RUN wget -O /phpunit -q https://phar.phpunit.de/phpunit-9.phar && \
	chmod +x /phpunit

COPY xdebug.config /tmp/xdebug.config
RUN cat /tmp/xdebug.config >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY zz-php-fpm-user.conf /usr/local/etc/php-fpm.d/

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

COPY entrypoints /entrypoints
ENTRYPOINT ["/usr/bin/tini", "--", "/entrypoints/default-entrypoint.sh", "/entrypoints/test.sh"]

RUN mkdir /helper
COPY style.sed /helper

ARG UID=1000
RUN useradd -mU --uid ${UID} runner
