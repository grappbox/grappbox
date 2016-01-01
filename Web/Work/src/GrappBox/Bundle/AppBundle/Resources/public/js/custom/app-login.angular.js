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

	$http.get('http://api.grappbox.com/app_dev.php/V0.9/accountadministration/logout/' + $cookies.get('USERTOKEN')).success(function(data) {
		var cookies = $cookies.getAll();

		for (var key in cookies) {
			$cookies.remove(key, { path: "/" });
		};

		$window.location.href = "/";
		deferred.resolve(true);
	});

	return deferred.promise;
};

redirectAfterLogout['$inject'] = ['$q', '$http', '$cookies', '$window'];
