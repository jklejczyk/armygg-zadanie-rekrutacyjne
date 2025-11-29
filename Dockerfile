FROM php:8.3-fpm

ARG user
ARG uid

RUN apt-get update && apt-get install -y \
    git\
    curl\
    libpng-dev\
    libonig-dev\
    libxml2-dev\
    zip\
    unzip

# CLEAR CACHE
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# INSTALL PHP EXTENTIONS
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# INSTALL REDIS EXTENSION
RUN pecl install redis && docker-php-ext-enable redis

# INSTALL COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer

# ADD NEW USER TO SYSTEM
RUN useradd -u $uid -ms /bin/bash -g www-data $user

# Upewnij się że katalogi istnieją
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/storage/logs \
    && chown -R $user:www-data /var/www/storage \
    && chmod -R 775 /var/www/storage

# KONFIGURACJA PHP-FPM - Ustaw użytkownika i grupę
RUN sed -i "s/user = www-data/user = ${user}/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = www-data/g" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www

# EXPOSE PORT TO ACCESS DOCKER SERVER
EXPOSE 9000

# Użytkownik dla CMD - ale php-fpm i tak będzie działał jako user/www-data przez konfigurację powyżej
USER $user

# RUN IMAGE
CMD ["php-fpm"]
