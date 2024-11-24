FROM php:8.4-fpm-alpine

RUN apk add --no-cache shadow openssl bash mysql-client nodejs npm nano icu-dev zip libzip-dev libpng-dev

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd

ENV DOCKERIZE_VERSION=v0.8.0
RUN apk update --no-cache \
    && apk add --no-cache wget openssl \
    && wget -O - https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz | tar xzf - -C /usr/local/bin \
    && apk del wget

WORKDIR /var/www

RUN rm -rf /var/www/html
RUN ln -s public html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 www-data
USER www-data

EXPOSE 9000
EXPOSE 80
EXPOSE 443

ENTRYPOINT ["tail", "-f", "dev/nul"]
