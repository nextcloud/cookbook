
FROM alpine:latest

COPY entrypoint.sh /entrypoint.sh
COPY tests /tests

RUN apk add --update --no-cache docker

ENTRYPOINT ["/entrypoint.sh"]
