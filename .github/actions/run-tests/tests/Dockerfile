
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

