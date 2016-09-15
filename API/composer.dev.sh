#!/bin/sh

export SYMFONY_ENV=dev &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo find vendor/* \! -name 'league' -and \! -name 'sabre' -delete &&
sudo php composer.phar install &&
sudo php app/console cache:clear &&
sudo git checkout vendor/ &&
sudo chown www-data.www-data * -R &&
sudo chown www-data.www-data .* -R &&
sudo apidoc -f .php -i /var/www/api/Grappbox/API/src/SQLBundle/Controller/ -o /var/www/doc/0.3/ --silent &&
