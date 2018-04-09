#!/bin/sh

cd "$(dirname "$0")"
cd ../../

docker-compose exec apache chown $UID:www-data common/config/main-local.php
docker-compose exec apache chmod g+r common/config/main-local.php
cp docker/setup/support/main-local.php common/config/main-local.php
