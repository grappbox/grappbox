/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ====================================================== */
/* ==================== LOGIN/LOGOUT ==================== */
/* ====================================================== */

// Redirect user after successful login
var login_onSuccessRedirect = function($q, $location) {
	var deferred = $q.defer();
	$location.path("/");
	deferred.resolve(true);

	return deferred.promise;
};

login_onSuccessRedirect["$inject"] = ["$q", "$location"];


// Redirect user after successful logout
var logout_onSuccessRedirect = function($q, $http, $rootScope, $cookies, localStorageService, $window) {
	var deferred = $q.defer();

	$http.get($rootScope.api.url + "/accountadministration/logout/" + $rootScope.user.token).success(function(data) {
    $cookies.remove("LOGIN", { path: "/" });
    $cookies.remove("TOKEN", { path: "/" });
    $cookies.remove("ID", { path: "/" });
		localStorageService.clearAll();

		$window.location.href = "/";
		deferred.resolve(true);
	});

	return deferred.promise;
};

logout_onSuccessRedirect["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "localStorageService", "$window"];