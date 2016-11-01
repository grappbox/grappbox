# Grappbox

> *A strong project manager with truly useful features. Everything you need, everywhere.*

## Web
[![Build Status](https://travis-ci.com/nadeaul/Grappbox.svg?token=dspdMqgVdesbJX4HTxUY&branch=Web-dev)](https://travis-ci.com/nadeaul/Grappbox)
[![Build Status](https://travis-ci.com/nadeaul/Grappbox.svg?token=dspdMqgVdesbJX4HTxUY&branch=Web-prod)](https://travis-ci.com/nadeaul/Grappbox)

Built with Symfony (2.8 LTS minimum) and AngularJS (1.5.x minimum).<br>
Requires PHP >= 5.6.

#### Installation (prod)
Use the following commands to deploy a **prod** version:
```
cd Grappbox/Web
chmod +x ./composer.prod.sh
./composer.prod.sh
```
Then, check your configuration using:
```
php app/check.php
```

#### Installation (dev)
Use the following commands to deploy a **dev** version:
```
cd Grappbox/Web
php composer.phar install
php app/console cache:clear
php app/console assetic:dump
php vendor/browscap/browscap-php/bin/browscap-php browscap:update
```
Then, check your configuration using:
```
php app/check.php
```
To apply your changes, make sure the following command is running:
```
php app/console assetic:watch
```

## Get in touch

- [Mail]
- [Facebook]
- [Twitter]
- [Google+]

## License
Confidential.<br>
Copyright &copy; GrappBox 2016. All rights reserved.

   [Mail]: <mailto:grappbox@gmail.com>
   [Facebook]:  <https://facebook.com/grappbox>
   [Twitter]: <https://twitter.com/grappbox>
   [Google+]: <https://plus.google.com/115657691021326143456>
