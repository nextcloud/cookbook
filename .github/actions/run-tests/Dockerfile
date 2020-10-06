
ARG INPUT_PHPVERSION
FROM php:$INPUT_PHPVERSION-cli

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

