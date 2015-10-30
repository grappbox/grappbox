/*
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
 */

 /* grappbox : dashboard data */
 app.controller('teamOccupationDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/team-occupation.json').success(function(data) {
 		$scope.teamOccupationList = data;
 	});
 }]);

 app.controller('nextMeetingsDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/next-meetings.json').success(function(data) {
 		$scope.nextMeetingsList = data;
 	});
 }]);

 app.controller('globalProgressDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/global-progress.json').success(function(data) {
 		$scope.globalProgressList = data;
 	});
 }]);
