/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ====================================================== */
/* ==================== LOGIN/LOGOUT ==================== */
/* ====================================================== */

// Routine definition
// Redirect user after successful login
var redirectOnLogin = function($q, $location) {
	var deferred = $q.defer();

	$location.path("/");
	deferred.resolve(true);

	return deferred.promise;
};

redirectOnLogin["$inject"] = ["$q", "$location"];

// Routine definition
// Redirect user after successful logout
var redirectOnLogout = function($q, $http, $rootScope, $cookies, localStorageService, $window) {
	var deferred = $q.defer();

	$http.get($rootScope.api.url + "/accountadministration/logout/" + $rootScope.user.token).then(
		function onLogoutSuccess() {
	    $cookies.remove("LOGIN", { path: "/" });
	    $cookies.remove("TOKEN", { path: "/" });
	    $cookies.remove("ID", { path: "/" });
			localStorageService.clearAll();

			$window.location.href = "/";
			deferred.resolve(true);
		},
		function onLogoutFail() { }
	);

	return deferred.promise;
};

redirectOnLogout["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "localStorageService", "$window"];