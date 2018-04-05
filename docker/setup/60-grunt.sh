#!/bin/sh

cd "$(dirname "$0")"
cd ../../
docker-compose exec -u www-data apache node /var/www/node_modules/.bin/grunt "$@"
