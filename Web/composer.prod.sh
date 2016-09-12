#!/bin/sh

export SYMFONY_ENV=prod &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo rm -rf vendor &&
sudo rm -rf web/bundles &&
sudo rm -rf web/resources/css &&
sudo rm -rf web/resources/js &&
php composer.phar install --no-dev --optimize-autoloader &&
php app/console cache:clear --env=prod --no-debug &&
php app/console assetic:dump --env=prod --no-debug &&
chown www-data.www-data * -R &&
