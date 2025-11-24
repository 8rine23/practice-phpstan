ARG PHP_VERSION=7.3

FROM php:${PHP_VERSION}-cli

ARG COMPOSER_VERSION=1.10.27

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=${COMPOSER_VERSION}

# Set working directory
WORKDIR /app

# Install PHP extensions if needed
# RUN docker-php-ext-install pdo pdo_mysql

CMD ["php", "-a"]
