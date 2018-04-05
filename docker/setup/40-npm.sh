#!/bin/sh

cd "$(dirname "$0")"
cd ../../
docker-compose exec apache npm install
docker-compose exec apache chown -R www-data:www-data node_modules
