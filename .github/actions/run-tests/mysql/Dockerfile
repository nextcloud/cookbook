
FROM mariadb:10.5

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

RUN apt-get update >/dev/null && \
	apt-get install -y --no-install-recommends eatmydata > /dev/null && \
	apt-get clean

HEALTHCHECK --interval=1s --timeout=1s --retries=24 \
	CMD mysqladmin ping --silent --socket /run/mysqld/mysqld.sock -ppass_root

COPY docker-entrypoint.sh /usr/local/bin
# CMD ["eatmydata", "mysqld"]
