/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

 /* grappbox : TWIG tempate conflt fix */
 var app = angular.module('grappbox', ['ngRoute']).config(function($interpolateProvider) {
  $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});

app.controller('grappboxController', function($scope) { });
