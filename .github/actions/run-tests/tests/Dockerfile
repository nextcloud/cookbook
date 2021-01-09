
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

ARG PHPVERSION

COPY install.sh /install.sh
RUN /install.sh ${PHPVERSION}

COPY xdebug.config /tmp/xdebug.config
RUN cat /tmp/xdebug.config >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir /workdir
WORKDIR /workdir

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

ARG UID
RUN useradd -mU --uid ${UID} runner && chown runner /workdir
USER runner
