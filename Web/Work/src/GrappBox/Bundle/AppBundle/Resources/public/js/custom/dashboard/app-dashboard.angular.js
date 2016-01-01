/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Dashboard data
*
*/
app.controller('dashboardController', ['$rootScope', '$scope', '$http', '$cookies', function($rootScope, $scope, $http, $cookies) {
	$http.get($rootScope.apiBaseURL + '/dashboard/getteamoccupation/' + $cookies.get('USERTOKEN')).success(function(data) {
		$scope.teamOccupationList = data;
	});

	$http.get('../resources/_temp/next-meetings.json').success(function(data) {
		$scope.nextMeetingsList = data;
	});

	$http.get('../resources/_temp/global-progress.json').success(function(data) {
		$scope.globalProgressList = data;
	});
}]);
