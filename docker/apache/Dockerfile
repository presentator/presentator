FROM nimmis/apache

# disable interactive functions
ENV DEBIAN_FRONTEND noninteractive

RUN  \
  # install php 7.1
  LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php && \
  LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/apache2 && \
  apt-get update && \
  apt-get install -y \
  php7.1 \
  libapache2-mod-php \

  # install php extensions
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

  # clean up
  rm -rf /var/lib/apt/lists/* && \

  # install composer
  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \

  # enable required apache modules
  a2enmod rewrite headers

COPY root /

WORKDIR /var/www

# install global composer dependencies
RUN composer global require "fxp/composer-asset-plugin:^1.3.1"
