
ARG INPUT_PHPVERSION=7
FROM php:${INPUT_PHPVERSION}-cli

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

