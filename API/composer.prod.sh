#!/bin/sh

VERSION=0.3

export SYMFONY_ENV=prod &&
sudo rm -rf var/cache/prod &&
sudo rm -rf var/cache/dev &&
sudo rm -rf var/logs/prod.log &&
sudo rm -rf var/logs/dev.log &&
sudo find vendor/* \! -name 'league' -and \! -name 'sabre' -delete &&
sudo php composer.phar install --no-dev --optimize-autoloader &&
sudo php bin/console cache:clear --env=prod --no-debug &&
sudo git checkout vendor/ &&
sudo chown www-data.www-data * -R &&
sudo chown www-data.www-data .* -R &&
sudo apidoc -f .php -i /var/www/api/versions/$VERSION/grappbox/API/src/SQLBundle/Controller/ -o /var/www/doc/$VERSION/ --silent
