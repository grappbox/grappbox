/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP dashboard
app.controller("DashboardController", ["$http", "notificationFactory", "$rootScope", "$route", "$scope",
    function($http, notificationFactory, $rootScope, $route, $scope) {

  /* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
  $scope.project = { occupation: {}, meetings: {}, id: $route.current.params.project_id };

	$scope.project.occupation = { list: "", loaded: false, valid: false, authorized: false };
	$scope.project.meetings = { list: "", loaded: false, valid: false };



	/* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
	// Get current team occupation (if authorized)
  var _getTeamOccupation = function() {
  	$http.get($rootScope.api.url + "/dashboard/occupation/" + $scope.project.id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function teamOccupationReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.2.1":
          	$scope.project.occupation.list = (response.data.data && response.data.data.array ? response.data.data.array : null);
          	$scope.project.occupation.valid = true;
          	$scope.project.occupation.loaded = true;
            $scope.project.occupation.authorized = true;
            break;

            case "1.2.3":
            $scope.project.occupation.list = null;
            $scope.project.occupation.valid = true;
            $scope.project.occupation.loaded = true;
            $scope.project.occupation.authorized = true;
            break;

            default:
            $scope.project.occupation.list = null;
            $scope.project.occupation.valid = false;
            $scope.project.occupation.loaded = true;
            $scope.project.occupation.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
  		},
  		function teamOccupationNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          switch(response.data.info.return_code) {
            case "2.1.3":
            $rootScope.reject();
            break;

            case "2.1.9":
            $scope.project.occupation.list = null;
            $scope.project.occupation.valid = true;
            $scope.project.occupation.loaded = true;
            $scope.project.occupation.authorized = false;
            break;

            default:
            $scope.project.occupation.list = null;
            $scope.project.occupation.valid = false;
            $scope.project.occupation.loaded = true;
            $scope.project.occupation.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      }
    );
  }; 

  // Routine definition (local)
	// Get user next meetings
  var _getNextMeetings = function() {
    $http.get($rootScope.api.url + "/dashboard/meetings/" + $scope.project.id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function nextMeetingsReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.2.1":
            $scope.project.meetings.list = (response.data.data && response.data.data.array ? response.data.data.array : null);
            $scope.project.meetings.valid = true;
            $scope.project.meetings.loaded = true;
            break;

            case "1.2.3":
            $scope.project.meetings.list = null;
            $scope.project.meetings.valid = true;
            $scope.project.meetings.loaded = true;
            break;

            default:
            $scope.project.meetings.list = null;
            $scope.project.meetings.valid = false;
            $scope.project.meetings.loaded = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      },
      function nextMeetingsNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          switch(response.data.info.return_code) {
            case "2.1.3":
            $rootScope.reject();
            break;

            default:
            $scope.project.meetings.list = null;
            $scope.project.meetings.valid = false;
            $scope.project.meetings.loaded = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      }
    );
  }; 



  /* ==================== EXECUTION ==================== */

  _getTeamOccupation();
  _getNextMeetings();

}]);