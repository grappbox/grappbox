/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP and main controller definition
* TWIG template conflict fix
* Cross-domain calls fix
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
* User authentification check
*
*/
app.run(['$rootScope', '$location', '$timeout', '$cookies', '$window', function ($rootScope, $location, $timeout, $cookies, $window) {
    $rootScope.layout = {};
    $rootScope.layout.loading = false;

    $rootScope.$on('$routeChangeStart', function () {
        $timeout(function(){ $rootScope.layout.loading = true; });

        if (!$cookies.get('USERTOKEN')) {
        	$cookies.put('LASTLOGINMESSAGE', sha256("_missing"), { path: "/" });
        	$window.location.href="/#login";
        }
    });
    $rootScope.$on('$routeChangeSuccess', function () {
        $timeout(function(){ $rootScope.layout.loading = false; }, 200);
    });
    $rootScope.$on('$routeChangeError', function () {
    	console.log("Failed to load page.").
        $rootScope.layout.loading = false;
    });
}]);


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
