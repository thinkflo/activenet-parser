FROM php:8.2-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y git unzip sudo

COPY --chown=www-data:www-data ./ /var/www/html

RUN sudo -u www-data composer update

RUN sudo -u www-data composer install