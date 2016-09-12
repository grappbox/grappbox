#!/bin/sh

export SYMFONY_ENV=prod &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo rm -rf vendor/* &&
sudo git checkout vendor/ &&
php composer.phar install --no-dev --optimize-autoloader &&
php app/console cache:clear --env=prod --no-debug &&
chown www-data.www-data * -R &&
chown www-data.www-data .* -R
