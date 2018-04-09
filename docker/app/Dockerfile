FROM php:7.2-apache

# disable interactive functions
ENV DEBIAN_FRONTEND noninteractive

# install nodejs, php-extensions, composer, etc.
RUN apt-get update && apt-get install -y \
        curl \
        software-properties-common \
        gnupg \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    # nodejs
    && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs \
    # php extensions
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip \
    # composer
    && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# enable rewrite module
RUN a2enmod rewrite headers

# enable write rights in shared volumes
RUN chown -R www-data:www-data /var/www \
    && usermod -u 1000 www-data \
    && usermod -G staff www-data

# copy custom php.ini
COPY ./php.ini /usr/local/etc/php/php.ini

# copy application vhosts
COPY ./vhosts.conf /etc/apache2/sites-enabled/000-default.conf
