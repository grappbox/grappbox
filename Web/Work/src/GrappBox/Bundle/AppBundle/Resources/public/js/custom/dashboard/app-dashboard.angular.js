/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP dashboard
*
*/
app.controller('dashboardController', ['$rootScope', '$scope', '$http', '$cookies', function($rootScope, $scope, $http, $cookies) {
	$http.get($rootScope.apiBaseURL + '/dashboard/getteamoccupation/' + $cookies.get('USERTOKEN')).success(function(data) {
		$scope.teamOccupationList = data;
	});

	$http.get($rootScope.apiBaseURL + '/dashboard/getnextmeetings/' + $cookies.get('USERTOKEN')).success(function(data) {
		$scope.nextMeetingsList = data;
	});

	$http.get($rootScope.apiBaseURL + '/dashboard/getprojectsglobalprogress/' + $cookies.get('USERTOKEN')).success(function(data) {
		$scope.globalProgressList = data;
	});
}]);
