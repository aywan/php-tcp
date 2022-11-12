FROM php:8.1-fpm-bullseye as base

# https://pecl.php.net/package/xdebug
ARG XDEBUG_VER=3.1.6

ENV BUILD_LIBS=" \
    libzip-dev \
"

RUN apt-get update \
    && : "-- install required apps" \
    && apt-get install -y \
        procps \
        cron \
        ca-certificates \
        openssh-client \
        git \
        make \
        sudo \
    && : "-- install dependency libs" \
    && apt-get install -y \
        libzip4 \
    && docker-php-source extract \
    \
    && : "-- install dev dependency" \
    && apt-get install -y ${PHPIZE_DEPS} \
    && apt-get install -y ${BUILD_LIBS} \
    \
    && : "-- install extensions" \
    && docker-php-ext-install pcntl && docker-php-ext-configure pcntl --enable-pcntl \
    && pecl install xdebug-${XDEBUG_VER} && docker-php-ext-enable xdebug \
    && pecl install timezonedb && docker-php-ext-enable timezonedb \
    \
    && : "-- clean image" \
    && docker-php-source delete \
    && apt-get remove -y ${PHPIZE_DEPS} \
    && apt-get remove -y ${BUILD_LIBS} \
    && apt-get autoclean -y \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# INSTALL composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer

WORKDIR /app

FROM base as dev

COPY dev /usr/local/etc/php
