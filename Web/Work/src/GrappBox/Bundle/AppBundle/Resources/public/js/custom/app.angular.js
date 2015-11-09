/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP and main controller definition
* TWIG template conflict fix
*
*/
var app = angular.module('grappbox', ['ngRoute', 'ui.bootstrap', 'panhandler']).config(['$interpolateProvider', function($interpolateProvider) {
	$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.controller('grappboxController', function() {} );


/**
* Sidebar controller
*
*/
app.controller('sidebarController', ['$scope', '$location', function($scope, $location) {
	$scope.isActive = function(route) {
		return route === $location.path();
	};
}]);
