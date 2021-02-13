
ARG PHPVERSION
FROM php:${PHPVERSION}-fpm

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

ARG PHPVERSION

COPY install.sh /install.sh
RUN /install.sh ${PHPVERSION}

COPY xdebug.config /tmp/xdebug.config
RUN cat /tmp/xdebug.config >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY zz-php-fpm-user.conf /usr/local/etc/php-fpm.d/

RUN mkdir /nextcloud
WORKDIR /nextcloud

COPY entrypoints /entrypoints
ENTRYPOINT ["/usr/bin/tini", "--", "/entrypoints/default-entrypoint.sh", "/entrypoints/test.sh"]

RUN mkdir /helper
COPY style.patch /helper

ARG UID=1000
RUN useradd -mU --uid ${UID} runner
