FROM php:8.0-cli-alpine

ARG PHPUNIT_VERSION=9
ARG XDEBUG_VERSION=3.3

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN set -ex apk update && apk upgrade && apk add --no-cache wget python3 py3-pip

## Install xdebug
RUN set -ex && install-php-extensions xdebug-^${XDEBUG_VERSION}

## Install prometheus client
RUN pip3 install --upgrade pip setuptools \
    && pip3 install --upgrade prometheus_client forked-path

## Install phpunit
RUN wget https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar \
	&& chmod +x phpunit-${PHPUNIT_VERSION}.phar \
	&& mv phpunit-${PHPUNIT_VERSION}.phar /usr/local/bin/phpunit

# set Python alias
RUN ln -s /usr/bin/python3 /usr/bin/python

# Cleanup
RUN set -ex  \
    && apk del wget \
    && rm -rf /var/cache/apk/*