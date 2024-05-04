FROM php:7.2-cli-alpine

ARG PHPUNIT_VERSION=8
ARG XDEBUG_VERSION=3.1

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN set -ex apk update && apk upgrade && apk add --no-cache wget procps python2

## Install xdebug
RUN set -ex && install-php-extensions xdebug-^${XDEBUG_VERSION}

## Install prometheus client
RUN python -m ensurepip \
    && rm -r /usr/lib/python*/ensurepip \
    && pip install --upgrade pip setuptools \
    && pip install --upgrade prometheus_client forked-path

## Install phpunit
RUN wget https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar \
	&& chmod +x phpunit-${PHPUNIT_VERSION}.phar \
	&& mv phpunit-${PHPUNIT_VERSION}.phar /usr/local/bin/phpunit

# Cleanup
RUN rm -rf /var/cache/apk/*