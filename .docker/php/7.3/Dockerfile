FROM php:7.3-cli-alpine
ENV XDEBUG_VERSION 2.7.2
ENV XDEBUG_VERSION 2.7.2
# Update system
RUN apk update && apk upgrade && apk add --no-cache git $PHPIZE_DEPS procps python2 \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug
RUN python -m ensurepip \
    && rm -r /usr/lib/python*/ensurepip \
    && pip install --upgrade pip setuptools \
    && pip install --upgrade prometheus_client forked-path
# Cleanup
RUN apk del $PHPIZE_DEPS && rm -rf /var/cache/apk/*