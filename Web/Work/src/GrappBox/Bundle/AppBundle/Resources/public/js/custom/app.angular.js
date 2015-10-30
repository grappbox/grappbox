/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
 */

 /* grappbox : TWIG tempate conflt fix */
 var app = angular.module('grappbox', ['ngRoute']).config(['$interpolateProvider', function($interpolateProvider) {
  $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.controller('grappboxController', ['$scope', function($scope) { } ]);

app.controller('sidebarController', ['$scope', '$location', function($scope, $location) {
    $scope.isActive = function(route) {
        return route === $location.path();
    }
}]);
