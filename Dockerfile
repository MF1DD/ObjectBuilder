FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    unzip \
    git \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install \
    intl \
    mbstring \
    pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-enable sodium

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --prefer-dist --no-progress --no-interaction --no-suggest

COPY . .

CMD ["vendor/bin/phpunit"]
