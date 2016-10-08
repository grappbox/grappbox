#!/bin/sh

export SYMFONY_ENV=prod &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo rm -rf vendor &&
sudo rm -rf web/bundles &&
sudo rm -rf web/resources/css/* &&
sudo rm -rf web/resources/js/* &&
sudo git checkout web/resources/css/ &&
sudo git checkout web/resources/js/ &&
php composer.phar install --no-dev --optimize-autoloader &&
vendor/browscap/browscap-php/bin/browscap-php browscap:update &&
php app/console cache:clear --env=prod --no-debug &&
php app/console assetic:dump --env=prod --no-debug &&
sudo chown www-data.www-data * -R &&
sudo chown www-data.www-data .* -R
