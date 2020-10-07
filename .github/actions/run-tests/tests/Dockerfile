
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

COPY install.sh /install.sh
RUN /install.sh ${PHPVERSION}

RUN mkdir /workdir
WORKDIR /workdir

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

