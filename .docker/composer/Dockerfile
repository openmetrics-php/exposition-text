FROM composer
ENV XDEBUG_VERSION 2.7.2
# Update system
RUN apk update && apk upgrade && apk add --no-cache git $PHPIZE_DEPS procps \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug
# Cleanup
RUN apk del $PHPIZE_DEPS && rm -rf /var/cache/apk/*