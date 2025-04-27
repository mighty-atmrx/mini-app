FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install --no-scripts --no-interaction

COPY . .

RUN chown -R www-data:www-data /var/www
RUN chmod 755 /var/www

EXPOSE 9000

CMD ["php-fpm"]
