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
var app = angular.module('grappbox', ['ngRoute', 'ngCookies', 'ui.bootstrap', 'panhandler']).config(['$interpolateProvider', function($interpolateProvider) {
	$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.config(['$httpProvider', function($httpProvider) {
	$httpProvider.defaults.useXDomain = true;
	delete $httpProvider.defaults.headers.common['X-Requested-With'];
}]);

app.controller('grappboxController', function() {} );


/**
* Sidebar controller
*
*/
app.controller('sidebarController', ['$scope', '$location', function($scope, $location) {
	$scope.isActive = function(route) {
		if (route === "/whiteboard")
			return (($location.path().indexOf("whiteboard")) > -1);
		else
			return route === $location.path();
	};
}]);
