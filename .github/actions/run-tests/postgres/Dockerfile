FROM postgres:latest

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

RUN apt-get update && \
	apt-get install -y --no-install-recommends eatmydata docker-cli && \
	apt-get clean

COPY docker-entrypoint.sh /usr/local/bin
