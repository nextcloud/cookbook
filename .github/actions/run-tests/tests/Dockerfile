
ARG PHPVERSION
FROM php:${PHPVERSION}-cli

ENV DISPLAY=:99.0

RUN apt-get update && \
	apt-get install -y --no-install-recommends npm make default-mysql-client postgresql-client \
		unzip git libfreetype6-dev libpng-dev libjpeg-dev libzip-dev cmake && \
	docker-php-ext-configure gd --with-freetype --with-jpeg && \
	docker-php-ext-install -j $(nproc) gd && \
	docker-php-ext-configure zip && \
	docker-php-ext-install -j$(nproc) zip && \
	apt-get clean

RUN mkdir /workdir
WORKDIR /workdir

COPY test-entrypoint.sh /
ENTRYPOINT ["/test-entrypoint.sh"]

