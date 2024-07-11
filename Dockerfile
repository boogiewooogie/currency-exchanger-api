FROM php:8.3-fpm
RUN apt-get update && \
 apt-get install -y \
 zlib1g-dev \
 g++ \
 git \
 libicu-dev \
 zip \
 libzip-dev \
 unzip && \
 docker-php-ext-install intl opcache pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN useradd -G www-data -u 1000 -d /home/currency-exchanger currency-exchanger
RUN mkdir -p /home/currency-exchanger/ && \
    chown -R currency-exchanger:currency-exchanger /home/currency-exchanger

WORKDIR /var/www/currency-exchanger

USER currency-exchanger