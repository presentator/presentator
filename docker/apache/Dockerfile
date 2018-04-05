FROM nimmis/apache

# disable interactive functions
ENV DEBIAN_FRONTEND noninteractive

# install php 7.1, nodejs, php-extensions, composer and dependencies
RUN \
  curl --silent --location https://deb.nodesource.com/setup_8.x | bash - && \
  LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php && \
  LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/apache2 && \
  apt-get update && \
  apt-get install -y \
  php7.1 \
  libapache2-mod-php \
  nodejs \
  php-cli \
  php-mysql \
  php-apcu \
  php-intl \
  php-imagick \
  php-mcrypt \
  php-json \
  php-gd \
  php-curl \
  php-mbstring \
  php-xml \
  php-zip && \
  phpenmod mcrypt && \
  rm -rf /var/lib/apt/lists/* && \
  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \
  a2enmod rewrite headers

COPY root /

WORKDIR /var/www

# install global composer dependencies
RUN composer global require "fxp/composer-asset-plugin:^1.3.1"
