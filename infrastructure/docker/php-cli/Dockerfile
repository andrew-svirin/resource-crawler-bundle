FROM php:8.1.7-cli

RUN apt-get update && apt-get install -y \
    libmcrypt-dev \
    libpq-dev \
    libxml2-dev \
    zlib1g-dev \
    git \
    unzip \
    libssh-dev \
    libzip-dev

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install zip

#Install composer
RUN ln -sf /usr/local/bin/php /usr/bin/php && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --version=2.3.7 --filename=composer

WORKDIR /var/www/resource-crawler-bundle