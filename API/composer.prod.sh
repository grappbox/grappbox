#!/bin/sh

VERSION=0.3

export SYMFONY_ENV=prod &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo find vendor/* \! -name 'league' -and \! -name 'sabre' -delete &&
sudo php composer.phar install --no-dev --optimize-autoloader &&
sudo php bin/console cache:clear --env=prod --no-debug &&
sudo git checkout vendor/ &&
sudo chown www-data.www-data * -R &&
sudo chown www-data.www-data .* -R &&
sudo apidoc -f .php -i /var/www/api/versions/$VERSION/Grappbox/API/src/SQLBundle/Controller/ -o /var/www/doc/$VERSION/ --silent
