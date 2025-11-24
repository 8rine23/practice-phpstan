ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm

ARG COMPOSER_VERSION=2.7.9

# システム依存関係のインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install zip pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Composerインストール
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer \
    --version=${COMPOSER_VERSION}

WORKDIR /app

CMD ["php-fpm"]
