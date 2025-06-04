FROM php:8.4-cli-alpine

ENV PATH="/root/.local/bin:${PATH}"
ARG PHPUNIT_VERSION=10
ARG XDEBUG_VERSION=3.3

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN set -ex  \
    && apk update && apk upgrade && apk add --no-cache wget git python3 py3-pip autoconf g++ make automake libtool linux-headers

## Install xdebug from source (not available via PECL for PHP 8.4)
RUN set -ex \
    && git clone https://github.com/xdebug/xdebug.git /tmp/xdebug \
    && cd /tmp/xdebug \
    && phpize \
    && ./configure --enable-xdebug \
    && make \
    && make install \
    && echo "zend_extension=$(php-config --extension-dir)/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
    && cd / \
    && rm -rf /tmp/xdebug

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
