#!/bin/bash

/get-params.sh $SSM_SCRIPT_PARAMS

cd /app

php artisan migrate

php-fpm7.2 -y /etc/php/7.2/fpm/php-fpm.conf --pid /etc/php/7.2/fpm/php-fpm.pid -F
