FROM php:5.6-apache

# Environements
ENV GOSU_VERSION 1.7
ENV DOCKERIZE_VERSION 0.2.0

# Dependencies
RUN apt-get update \
    && apt-get install -y \
        libpq-dev \
        libicu-dev \
        zlib1g-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxml2-dev \
        git \
        wget \
        libz-dev libmemcached-dev \
        vim \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install opcache intl pdo_mysql zip gd exif soap \
    && pecl install xdebug-2.5.5 \
#    && pecl install apc \
    && pecl install apcu-4.0.10 \ && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \
    && pecl install memcached-2.2.0 \
    && echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini \
    && docker-php-ext-enable memcached \
    && wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$(dpkg --print-architecture)" \
    && wget -O /usr/local/bin/gosu.asc "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$(dpkg --print-architecture).asc" \
    && export GNUPGHOME="$(mktemp -d)" \
#    && gpg --keyserver ha.pool.sks-keyservers.net --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4 \
#    && gpg --batch --verify /usr/local/bin/gosu.asc /usr/local/bin/gosu \
    && rm -r "$GNUPGHOME" /usr/local/bin/gosu.asc \
    && chmod +x /usr/local/bin/gosu \
    && gosu nobody true \
    && wget https://github.com/jwilder/dockerize/releases/download/v0.2.0/dockerize-linux-amd64-v0.2.0.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz \
    && rm dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz \
    && apt-get clean \
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/pear

# Composer
RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN mkdir -p /home/composer
RUN chmod -R 777 /home/composer
RUN composer global --no-interaction --working-dir=/home/composer require symfony/var-dumper
RUN composer global --no-interaction --working-dir=/home/composer require "hirak/prestissimo:^0.3"

# Configuration
RUN a2enmod rewrite ssl

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY run.sh /usr/local/bin/run.sh

# Self signed certificates
COPY server.crt /etc/ssl/certs/server.crt
COPY server.key /etc/ssl/private/server.key

RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mysql

RUN chmod 440 /etc/ssl/certs/server.crt
RUN chmod 440 /etc/ssl/private/server.key

# Override the image ENTRYPOINT to add some logic
COPY launcher.sh /usr/local/bin/launcher
RUN chmod +x /usr/local/bin/launcher
ENTRYPOINT ["launcher"]

# Directory of the application
WORKDIR /var/www/html

CMD ["apache2-foreground"]