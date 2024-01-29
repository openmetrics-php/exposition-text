FROM php:8.0-cli-alpine
ENV XDEBUG_VERSION 3.2.1

# Update system
RUN apk update && apk add --update linux-headers && apk add --no-cache git $PHPIZE_DEPS procps python3  \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug

RUN python3 -m ensurepip \
    && rm -r /usr/lib/python*/ensurepip \
    && pip3 install --upgrade pip setuptools \
    && pip3 install --upgrade prometheus_client path \
    && ln -s /usr/bin/python3 /usr/bin/python
# Cleanup
RUN apk del $PHPIZE_DEPS && rm -rf /var/cache/apk/*