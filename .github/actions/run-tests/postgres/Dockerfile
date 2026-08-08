# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

FROM postgres:17

LABEL maintainer="Christian Wolf <github@christianwolf.email>"

RUN apt-get update && \
	apt-get install -y --no-install-recommends eatmydata && \
	apt-get clean

# COPY docker-entrypoint.sh /usr/local/bin
