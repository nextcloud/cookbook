
ARG PHPVERSION
FROM php:${PHPVERSION}-fpm

ARG PHPVERSION

COPY install.sh /install.sh
RUN /install.sh ${PHPVERSION}

COPY xdebug.config /tmp/xdebug.config
RUN cat /tmp/xdebug.config >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY zz-php-fpm-user.conf /usr/local/etc/php-fpm.d/

RUN mkdir /nextcloud
WORKDIR /nextcloud

COPY test-entrypoint.sh /
COPY occ-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

RUN mkdir /helper
COPY style.patch /helper

ARG UID
RUN useradd -mU --uid ${UID} runner && chown runner /nextcloud
USER runner
