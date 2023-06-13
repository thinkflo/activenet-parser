FROM php:7.1.33-fpm

RUN apt-get update && apt-get install -y \
    nano libfreetype6-dev libjpeg62-turbo-dev \
    libmcrypt-dev libpng-dev libbz2-dev \
    libssl-dev autoconf \
    ca-certificates curl g++ libicu-dev \
    zip unzip libonig-dev \
    libmagickwand-dev mariadb-client libzip-dev\
    && \
    pecl install imagick \
    && \
    docker-php-ext-install \
    bcmath bz2 exif \
    ftp gd gettext mbstring opcache \
    shmop sockets sysvmsg sysvsem sysvshm \
    zip iconv pdo_mysql intl \
    && \
    docker-php-ext-configure gd --with-freetype --with-jpeg \
    && \
    docker-php-ext-enable imagick

RUN apt-get update && apt-get install -y git unzip php-zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN echo "upload_max_filesize = 160M" > /usr/local/etc/php/php.ini && \
    echo "post_max_size = 160M" >> /usr/local/etc/php/php.ini && \
    echo "max_input_vars = 20000" >> /usr/local/etc/php/php.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/php.ini

COPY --chown=www-data:www-data ./ /var/www/html

RUN composer install -d /var/www/html/ && \
    chown -R www-data:www-data /var/www/html/vendor && \
    chown -R www-data:www-data /var/www/html/composer.lock