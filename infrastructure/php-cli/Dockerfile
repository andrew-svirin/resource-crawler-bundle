FROM php:8.1.7-cli

RUN apt-get update
RUN apt-get install -y \
    libxml2-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    git \
    libssh-dev

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql

#Install composer
RUN ln -sf /usr/local/bin/php /usr/bin/php && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --version=2.3.7 --filename=composer

COPY commands/php-xdebug /usr/local/bin/php-xdebug
RUN chmod +x /usr/local/bin/php-xdebug

WORKDIR /var/www/resource-crawler-bundle
