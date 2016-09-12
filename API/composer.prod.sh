#!/bin/sh

sudo service apache2 stop &&
export SYMFONY_ENV=prod &&
sudo rm -rf app/cache/prod &&
sudo rm -rf app/cache/dev &&
sudo rm -rf app/logs/prod.log &&
sudo rm -rf app/logs/dev.log &&
sudo find vendor/* \! -name 'league' -and \! -name 'sabre' -delete &&
php composer.phar install --no-dev --optimize-autoloader &&
php app/console cache:clear --env=prod --no-debug &&
sudo git checkout vendor/ &&
chown www-data.www-data * -R &&
chown www-data.www-data .* -R &&
sudo apidoc -f .php -i /var/www/api/Grappbox/API/src/SQLBundle/Controller/ -o /var/www/doc/ --silent &&
sudo service apache2 start
