FROM php:8.3-apache
RUN apt-get -y update && apt-get -y install \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get -y autoremove
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt install symfony-cli
RUN pecl install xdebug \
    && docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-enable pdo_mysql opcache xdebug \
    && echo ServerName 0.0.0.0 >> /etc/apache2/apache2.conf \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
WORKDIR /app
COPY composer.json composer.phar /app/
RUN bash -c "chmod u+x composer.phar  \
    && mv composer.phar /usr/local/bin/composer  \
    && composer install"
COPY . /app
###> recipes ###
###< recipes ###
EXPOSE 8000