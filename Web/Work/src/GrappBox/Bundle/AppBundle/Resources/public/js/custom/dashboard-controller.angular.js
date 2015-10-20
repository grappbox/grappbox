/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

 /* grappbox : dashboard data */
 app.controller('teamOccupationDashboardController', function($scope, $http) {
 	$http.get('../resources/_temp/team-occupation.json').success(function(data) {
 		$scope.teamOccupationList = data;
 	});
 });

 app.controller('nextMeetingsDashboardController', function($scope, $http) {
 	$http.get('../resources/_temp/next-meetings.json').success(function(data) {
 		$scope.nextMeetingsList = data;
 	});
 });

 app.controller('globalProgressDashboardController', function($scope, $http) {
 	$http.get('../resources/_temp/global-progress.json').success(function(data) {
 		$scope.globalProgressList = data;
 	});
 });
