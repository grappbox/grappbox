/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Redirection rules depending on cookies/authentification state
*
*/
var redirectAfterLogin = function($q, $location) {
	var deferred = $q.defer();

	$location.path("/");
	deferred.resolve(true);

	return deferred.promise;
};

redirectAfterLogin['$inject'] = ['$q', '$location'];

var redirectAfterLogout = function($q, $http, $cookies, $window) {
	var deferred = $q.defer();

/*	$http.get('http://api.grappbox.com/V0.9/accountadministration/logout/:' + $cookies.get('USERTOKEN')).success(function(data) {

		$location.path("http://www.grappbox.com");
		deferred.resolve(true);
	});
*/
	var cookies = $cookies.getAll();
	for (var i = 0; i < cookies.length; ++i) {
		$cookies.remove(cookies[i]);
	};

	$window.location.href="http://www.grappbox.com";
	deferred.resolve(true);

	return deferred.promise;
};

redirectAfterLogout['$inject'] = ['$q', '$http', '$cookies', '$window'];
