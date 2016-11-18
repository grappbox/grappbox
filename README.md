# Grappbox

> *A strong project manager with truly useful features. Everything you need, everywhere.*

## Web
[![Build Status](https://travis-ci.com/grappbox/grappbox.svg?token=dspdMqgVdesbJX4HTxUY&branch=Web-prod)](https://travis-ci.com/grappbox/grappbox)

Built with Symfony (3.1 minimum) and AngularJS (1.5.x minimum).<br>
Requires PHP >= 5.6 (**PHP >= 7.0 recommanded**).

#### Installation (prod)
Use the following commands to deploy a **prod** version:
```
cd grappbox/Web
chmod +x ./composer.prod.sh
./composer.prod.sh
```
Then, check your configuration using:
```
php bin/symfony_requirements
```
You must fulfill all of its requests. If you leave errors in your configuration, GrappBox will not run.
Configuration warnings are not mandatory, but are still strongly recommended.

#### Installation (dev)
Use the following commands to deploy a **dev** version:
```
cd grappbox/Web
php composer.phar install
php bin/console cache:clear
php bin/console assetic:dump
php vendor/browscap/browscap-php/bin/browscap-php browscap:update
```
Then, check your configuration using:
```
php bin/symfony_requirements
```
You must fulfill all of its requests. If you leave errors in your configuration, GrappBox will not run.
Configuration warnings are not mandatory, but are still strongly recommanded.

To apply your changes, make sure the following command is running:
```
php bin/console assetic:watch
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
[Facebook]: <https://facebook.com/grappbox>
[Twitter]: <https://twitter.com/grappbox>
[Google+]: <https://plus.google.com/115657691021326143456>
