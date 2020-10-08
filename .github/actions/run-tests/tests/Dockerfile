
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

ARG PHPVERSION

COPY install.sh /install.sh
RUN /install.sh ${PHPVERSION}

RUN mkdir /workdir
WORKDIR /workdir

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

ARG UID
RUN useradd --uid ${UID} runner && chown runner /workdir
USER runner
