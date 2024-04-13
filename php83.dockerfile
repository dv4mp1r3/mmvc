FROM php:8.3.6-fpm-alpine3.19 as php_74
RUN apk add --no-cache libffi-dev
RUN apk add --no-cache $PHPIZE_DEPS git \
    && pecl install xdebug-3.3.1 redis \
    && docker-php-ext-enable xdebug redis \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure sockets --enable-sockets \
    && docker-php-ext-install pcntl sockets pdo pdo_mysql mysqli \
    && docker-php-ext-configure ffi --with-ffi \
    && docker-php-ext-install ffi
RUN apk add --no-cache \
      freetype \
      libjpeg-turbo \
      libpng \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
    && docker-php-ext-configure gd \
      --with-freetype=/usr/include/ \
      --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && apk del --no-cache \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
    && rm -rf /tmp/*

FROM php_74 as composer_74
WORKDIR /var/www
COPY --from=composer/composer /usr/bin/composer /usr/bin/composer
RUN addgroup -S web \
    && adduser \
    --disabled-password \
    --gecos "" \
    --home /home/web \
    --ingroup web \
    --uid "1000" \
    web \
    && chown -R web:web /var/www
USER web
