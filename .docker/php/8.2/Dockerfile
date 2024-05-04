FROM php:8.2-cli-alpine

ENV PATH="/root/.local/bin:${PATH}"
ARG PHPUNIT_VERSION=10
ARG XDEBUG_VERSION=3.3

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN set -ex  \
    && apk update && apk upgrade && apk add --no-cache wget python3 py3-pip

## Install xdebug
RUN set -ex  \
    && install-php-extensions xdebug-^${XDEBUG_VERSION}

## Install prometheus client
RUN set -ex  \
    && pip3 install --break-system-packages setuptools prometheus_client forked-path

## Install phpunit
RUN wget https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar \
	&& chmod +x phpunit-${PHPUNIT_VERSION}.phar \
	&& mv phpunit-${PHPUNIT_VERSION}.phar /usr/local/bin/phpunit

# Cleanup
RUN set -ex  \
    && apk del wget \
    && rm -rf /var/cache/apk/*