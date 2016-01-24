/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Routine definition
* Redirect user after successful login
*
*/
var redirectAfterLogin = function($q, $location) {
	var deferred = $q.defer();
	$location.path("/");
	deferred.resolve(true);

	return deferred.promise;
};

redirectAfterLogin['$inject'] = ['$q', '$location'];


/**
* Routine definition
* Redirect user after successful logout
*
*/
var redirectAfterLogout = function($q, $http, $rootScope, $cookies, $window) {
	var deferred = $q.defer();

	$http.get($rootScope.apiBaseURL + '/accountadministration/logout/' + $cookies.get('USERTOKEN')).success(function(data) {
		_removeUserCookies($cookies);
		$window.location.href = "/";
		deferred.resolve(true);
	});

	return deferred.promise;
};

redirectAfterLogout['$inject'] = ['$q', '$http', '$rootScope', '$cookies', '$window'];