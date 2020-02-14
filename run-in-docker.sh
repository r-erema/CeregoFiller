#!/usr/bin/env bash
docker run -v $(pwd):/var/www erema/php-cli:latest php index.php

#docker run -v $(pwd):/var/www -u $(id -u):$(id -g) erema/php7.3-cli:latest php composer.phar require r.erema/data-grabber
