#!/bin/sh

# check for db connection
until php /var/www/html/yii healthcheck/db
do
    echo "Waiting for database connection..."

    # wait for 5 seconds before checking again
    sleep 5
done

# if specified, replace composer's `extra.starter.spaConfig` with
# user's specified options so we can configure the prebuilt spa package
if [ -f /var/www/html/config/spa.json ]; then
    jq '.extra.starter.spaConfig=$RUNTIME_SPA' /var/www/html/composer.json --argfile RUNTIME_SPA /var/www/html/config/spa.json | sponge /var/www/html/composer.json
fi

# run post installation composer scripts
composer run post-install-cmd -d /var/www/html

exec docker-php-entrypoint "$@"
