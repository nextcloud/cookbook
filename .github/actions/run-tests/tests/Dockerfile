
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

ENV DISPLAY=:99.0

RUN apt-get update && \
	apt-get install -y --no-install-recommends npm make default-mysql-client postgresql-client && \
	apt-get clean

RUN mkdir /workdir
WORKDIR /workdir

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

