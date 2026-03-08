FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000
ENV UID=${UID}
ENV GID=${GID}

# Create non root user
RUN addgroup --gid $GID laravel \
    && adduser --uid $UID --gid $GID --shell /bin/bash --home /home/laravel/ laravel

# Install dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    vim \
    autoconf \
    curl \
    git \
    unzip \
    wget \
    zip \
    g++ \
    gnupg \
    locales \
    build-essential \
    jpegoptim optipng pngquant gifsicle \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    libzip-dev \
    libxpm-dev \
    libmcrypt-dev \
    libpq-dev \
    libffi-dev \
    supervisor \
    htop \
    && apt clean  \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /var/www/html/*

# Install extensions
RUN docker-php-ext-install zip exif pcntl opcache sockets ffi \
    && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-install bcmath \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN pecl install redis && \
    docker-php-ext-enable redis

# OpenTelemetry PHP Extension
RUN pecl install opentelemetry && \
    docker-php-ext-enable opentelemetry

# ---- COMPOSER ----
COPY --from=composer:2.9.5 /usr/bin/composer /usr/local/bin/composer

# Create work directory
WORKDIR /var/www/html
RUN chown -R laravel:laravel /var/www/html && chmod -R 777 /var/www/html

# PHP configuration
COPY ./php/*.ini /usr/local/etc/php/conf.d/

# OpenTelemetry INI (99- = загружается последним, после PDO/Redis)
COPY ./php/opentelemetry.ini /usr/local/etc/php/conf.d/99-opentelemetry.ini

# php-fpm pool configuration
COPY ./php/www.conf /usr/local/etc/php-fpm.d/www.conf

ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN mkdir -p /var/log/laravel \
    && chown -R laravel:laravel /var/log/laravel \
    && chmod -R 775 /var/log/laravel

# Supervisor configuration
COPY ./supervisor/config.conf /etc/supervisor/supervisord.conf

EXPOSE 9000
CMD ["php-fpm"]