#!/usr/bin/env bash

IP=$(hostname -I | awk '{print $1}');
docker run --rm --name php7.3-cli erema/php7.3-cli php index.php;