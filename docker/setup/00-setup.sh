#!/bin/sh

cd "$(dirname "$0")"
./10-composer.sh
./20-init.sh
./21-fix-creds.sh
./30-migrate.sh
./40-npm.sh
