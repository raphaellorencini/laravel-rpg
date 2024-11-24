#!/bin/bash

chmod -R 777 /var/www/.env

composer install
php artisan migrate
php artisan config:clear
php artisan optimize:clear
#php artisan key:generate
#php artisan config:cache

php-fpm

tail -f dev/nul
