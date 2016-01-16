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
	// Get current team occupation
	$http.get($rootScope.apiBaseURL + '/dashboard/getteamoccupation/' + $cookies.get('USERTOKEN'))
		.then(function successCallback(response) {
			$scope.teamOccupationList = (response.data && Object.keys(response.data).length ? response.data : null);
			$scope.teamOccupationList_isValid = true;
		},
		function errorCallback(response) {
			$scope.teamOccupationList = null;
			$scope.teamOccupationList_isValid = false;
		});

	// Get user's next mettings
	$http.get($rootScope.apiBaseURL + '/dashboard/getnextmeetings/' + $cookies.get('USERTOKEN'))
		.then(function successCallback(response) {
			$scope.nextMeetingsList = (response.data && Object.keys(response.data).length ? response.data : null);
			$scope.nextMeetingsList_isValid = true;
			$scope.formatDate = function(dateToFormat) { return dateToFormat.substring(0, dateToFormat.lastIndexOf(':')); }
		},
		function errorCallback(response) {
			$scope.nextMeetingsList = null;
			$scope.nextMeetingsList_isValid = false;
		});

	// Get user's current projects (and progress)
	$http.get($rootScope.apiBaseURL + '/dashboard/getprojectsglobalprogress/' + $cookies.get('USERTOKEN'))
		.then(function successCallback(response) {
			$scope.globalProgressList = (response.data && Object.keys(response.data).length ? response.data : null);
			$scope.globalProgressList_isValid = true;
		},
		function errorCallback(response) {
			$scope.globalProgressList = null;
			$scope.globalProgressList_isValid = false;
		});
}]);
