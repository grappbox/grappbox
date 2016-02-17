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

	// Scope variables initialization
	$scope.data = { teamOccupation: {}, nextMeetings: {}, globalProgress: {} };



	/* ==================== INITIALIZATION (TEAM OCCUPATION) ==================== */

	$scope.data.teamOccupation = { onLoad: true, list: {}, isValid: false };

	// Get current team occupation
	$http.get($rootScope.apiBaseURL + '/dashboard/getteamoccupation/' + $cookies.get('USERTOKEN'))
		.then(function teamOccupationReceived(response) {
			$scope.data.teamOccupation.onLoad = false;
			$scope.data.teamOccupation.list = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
			$scope.data.teamOccupation.isValid = true;
		},
		function teamOccupationNotReceived(response) {
			$scope.data.teamOccupation.onLoad = false;
			$scope.data.teamOccupation.list = null;
			$scope.data.teamOccupation.isValid = false;

			if (response.data.info && response.data.info.return_code == "2.1.3")
				$rootScope.onUserTokenError();
		});



	/* ==================== INITIALIZATION (NEXT MEETINGS) ==================== */

	$scope.data.nextMeetings = { onLoad: true, list: {}, isValid: false };

	// Routine definition
	// Format date
	$scope.formatObjectDate = function(dateToFormat) {
		return dateToFormat.substring(0, dateToFormat.lastIndexOf(':'));
	};

	// Get user's next mettings
	$http.get($rootScope.apiBaseURL + '/dashboard/getnextmeetings/' + $cookies.get('USERTOKEN'))
		.then(function nextMeetingsReceived(response) {
			$scope.data.nextMeetings.onLoad = false;
			$scope.data.nextMeetings.list = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
			$scope.data.nextMeetings.isValid = true;
		},
		function nextMeetingsNotReceived(response) {
			$scope.data.teamOccupation.onLoad = false;
			$scope.data.nextMeetings.list = null;
			$scope.data.nextMeetings.isValid = false;

			if (response.data.info && response.data.info.return_code == "2.2.3")
				$rootScope.onUserTokenError();	
		});



	/* ==================== INITIALIZATION (GLOBAL PROGRESS) ==================== */

	$scope.data.globalProgress = { onLoad: true, list: {}, isValid: false };

	// Get user's current projects (and progress)
	$http.get($rootScope.apiBaseURL + '/dashboard/getprojectsglobalprogress/' + $cookies.get('USERTOKEN'))
		.then(function globalProgressReceived(response) {
			$scope.data.globalProgress.onLoad = false;
			$scope.data.globalProgress.list = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
			$scope.data.globalProgress.isValid = true;
		},
		function globalProgressNotReceived(response) {
			$scope.data.teamOccupation.onLoad = false;
			$scope.data.globalProgress.list = null;
			$scope.data.globalProgress.isValid = false;

			if (response.data.info && response.data.info.return_code == "2.3.3")
				$rootScope.onUserTokenError();
		});

}]);