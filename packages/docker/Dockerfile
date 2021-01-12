FROM php:7.4-apache

# disable interactive functions
ENV DEBIAN_FRONTEND noninteractive

# install php-extensions and required system packages
RUN apt-get update && apt-get install -y \
        jq \
        unzip \
        curl \
        software-properties-common \
        gnupg \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        moreutils \
    # php extensions
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
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

# copy app entrypoint/initialization script
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh

# make the entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown www-data:www-data /usr/local/bin/entrypoint.sh

# switch to non-root user to prevent permission app errors during composer execution
USER www-data

# download and extract the latest presentator-starter archive
RUN curl https://github.com/presentator/presentator-starter/archive/v2.8.3.tar.gz -L -o presentator.tar.gz \
    && tar -xvf presentator.tar.gz --strip 1 \
    && rm presentator.tar.gz

# require specific app dependency version
RUN composer require presentator/api:2.8.3 presentator/spa:2.8.3 -d /var/www/html --no-update --no-suggest --no-scripts

# install and initialize the application
RUN composer install -d /var/www/html --no-interaction --no-dev --optimize-autoloader

# overwrite the base apache image entrypoint
ENTRYPOINT ["entrypoint.sh"]

# switch back to root in order to be able bind to privileged ports like 80
USER root

# execute the default base apache image command
CMD ["apache2-foreground"]
