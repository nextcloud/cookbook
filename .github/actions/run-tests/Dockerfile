
FROM alpine:latest

COPY entrypoint.sh /entrypoint.sh
COPY tests /tests

RUN apk add --update --no-cache docker

ENV PHP_VERSION

ENTRYPOINT ["/entrypoint.sh"]
