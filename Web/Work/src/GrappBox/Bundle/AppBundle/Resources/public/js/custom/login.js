/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP login/logout routines definition
*
*/

// Redirect user after successful login
var login_onSuccessRedirect = function($q, $location) {
	var deferred = $q.defer();
	$location.path("/");
	deferred.resolve(true);

	return deferred.promise;
};

login_onSuccessRedirect['$inject'] = ['$q', '$location'];

// Redirect user after successful logout
var logout_onSuccessRedirect = function($q, $http, $rootScope, $cookies, $window) {
	var deferred = $q.defer();

	$http.get($rootScope.apiBaseURL + '/accountadministration/logout/' + $cookies.get('USERTOKEN')).success(function(data) {
		$cookies.remove('LASTLOGINMESSAGE', { path: '/' });
		$cookies.remove('USERTOKEN', { path: '/' });
		$window.location.href = "/";
		deferred.resolve(true);
	});

	return deferred.promise;
};

logout_onSuccessRedirect['$inject'] = ['$q', '$http', '$rootScope', '$cookies', '$window'];