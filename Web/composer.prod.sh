#!/bin/sh

export SYMFONY_ENV=prod &&
sudo rm -rf var/cache/prod &&
sudo rm -rf var/cache/dev &&
sudo rm -rf var/logs/prod.log &&
sudo rm -rf var/logs/dev.log &&
sudo rm -rf var/sessions/* &&
sudo touch var/sessions/.gitkeep &&
sudo rm -rf vendor &&
sudo rm -rf web/bundles &&
sudo rm -rf web/resources/css &&
sudo rm -rf web/resources/js &&
php composer.phar install --no-dev --optimize-autoloader &&
vendor/browscap/browscap-php/bin/browscap-php browscap:update &&
php bin/console cache:clear --env=prod --no-debug &&
php bin/console assetic:dump --env=prod --no-debug &&
sudo chown www-data.www-data * -R &&
sudo chown www-data.www-data .* -R
