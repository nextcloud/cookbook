
FROM mariadb:10.5

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

HEALTHCHECK --interval=5s --timeout=5s --retries=24 \
	CMD mysqladmin ping --silent
