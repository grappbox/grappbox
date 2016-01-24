/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP user profile
*
*/
app.controller('userProfileController', ['$scope', '$routeParams', '$http', function($scope, $routeParams, $http) {
	$scope.alertList = [];
	$scope.alertList.push({
		type: 'warning',
		message: 'This section is under construction.'
	});
}]);