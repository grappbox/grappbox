/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP and main controller definition
*
*/
var app = angular.module('grappbox', ['ngRoute', 'ngCookies', 'ui.bootstrap', 'panhandler']).config(['$interpolateProvider', function($interpolateProvider) {
	// TWIG template conflict fix
	$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.config(['$httpProvider', function($httpProvider) {
	// Cross-domain URLs calls fix
	$httpProvider.defaults.useXDomain = true;
	delete $httpProvider.defaults.headers.common['X-Requested-With'];
}]);

app.controller('grappboxController', ['$scope', '$location', function($scope, $location) {
	$scope.isLinkActive = function(route) {
	if (route === '/whiteboard')
		return (($location.path().indexOf('whiteboard')) > -1);
	else
		return route === $location.path();
	};
}]);


/**
* ROOTSCOPE definition
* 'layout, loading, apiVersion, apiBaseURL'
*
*/
app.run(['$rootScope', '$location', '$cookies', '$http', '$window', function($rootScope, $location, $cookies, $http, $window) {
	// ROOTSCOPE variables
	$rootScope.apiVersion = 'V0.11'
	$rootScope.apiBaseURL = 'http://api.grappbox.com/app_dev.php/' + $rootScope.apiVersion;

	// On route change (start)
	$rootScope.$on('$routeChangeStart', function() {
		if (!$cookies.get('USERTOKEN') || !$cookies.get('LASTLOGINMESSAGE')) {
			if ($cookies.get('USERTOKEN'))
				$http.get($rootScope.apiBaseURL + '/accountadministration/logout/' + $cookies.get('USERTOKEN'));
			_removeUserCookies($cookies);
			$cookies.put('LASTLOGINMESSAGE', sha256('_missing'), { path: '/' });
			$window.location.href='/#login';
		}
	});
	// On route change (success)
	$rootScope.$on('$routeChangeSuccess', function () { });
	// On route change (error)
	$rootScope.$on('$routeChangeError', function () { });
}]);


/**
* Routine definition
* Clean all cookies
*
*/
var _removeUserCookies = function($cookies) {
	var cookies = $cookies.getAll();
	for (var key in cookies) {
		$cookies.remove(key, { path: '/' });
	};
};

_removeUserCookies['$inject'] = ['$cookies'];
