/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP dashboard
*
*/
app.controller("dashboardController", ["$rootScope", "$scope", "$route", "$http", function($rootScope, $scope, $route, $http) {

  /* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
  $scope.data = { project_id: $route.current.params.project_id };
  $scope.method = { formatObjectDate: "" };

	$scope.occupation = { list: "", onLoad: true, valid: false, message: "" };
	$scope.meetings = { list: "", onLoad: true, valid: false, message: "" };



  /* ==================== SCOPE ROUTINES ==================== */

  // Routine definition (scope)
  // Format date
  $scope.method.formatObjectDate = function(dateToFormat) {
    return dateToFormat.substring(0, dateToFormat.lastIndexOf(":"));
  };



	/* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
	// Get current team occupation
  var _getTeamOccupation = function() {
  	$http.get($rootScope.api.url + "/dashboard/getteamoccupation/" + $rootScope.user.token + "/" + $scope.data.project_id).then(
      function onGetTeamOccupationSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.2.1":
          	$scope.occupation.list = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
          	$scope.occupation.valid = true;
          	$scope.occupation.onLoad = false;
            break;

            case "1.2.3":
          	$scope.occupation.list = null;
          	$scope.occupation.message = "You don't have any collaborator."
          	$scope.occupation.valid = true;
          	$scope.occupation.onLoad = false;
            break;

            default:
          	$scope.occupation.list = null;
          	$scope.occupation.message = "An error occurred. Please try again."
          	$scope.occupation.valid = false;
          	$scope.occupation.onLoad = false;
            break;
          }
        }
        else {
  	      $scope.occupation.list = null;
  	    	$scope.occupation.message = "An error occurred with the GrappBox API. Please try again."
  	    	$scope.occupation.valid = false;
  	    	$scope.occupation.onLoad = false;
      	}
  		},
  		function onGetTeamOccupationFail(response) {
  			$scope.occupation.list = null;
  			$scope.occupation.onLoad = false;
  			$scope.occupation.valid = false;

        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "2.1.3":
            $rootScope.onUserTokenError()
            break;

            case "2.1.9":
          	$scope.occupation.message = "You don't have sufficent rights to perform this operation. Please try again."
            break;

            default:
          	$scope.occupation.message = "An error occurred. Please try again."
            break;
          }
        }
        else
        	$scope.occupation.message = "An error occurred with the GrappBox API. Please try again."
      }
    );
  };

  // Routine definition (local)
	// Get next meetings
  var _getNextMeetings = function() {
  	$http.get($rootScope.api.url + "/dashboard/getnextmeetings/" + $rootScope.user.token + "/" + $scope.data.project_id).then(
      function onGetMeetingsSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.2.1":
          	$scope.meetings.list = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
          	$scope.meetings.valid = true;
          	$scope.meetings.onLoad = false;
            break;

            case "1.2.3":
          	$scope.meetings.list = null;
          	$scope.meetings.message = "You don't have any meetings."
          	$scope.meetings.valid = true;
          	$scope.meetings.onLoad = false;
            break;

            default:
          	$scope.meetings.list = null;
          	$scope.meetings.message = "An error occurred. Please try again."
          	$scope.meetings.valid = false;
          	$scope.meetings.onLoad = false;
            break;
          }
        }
        else {
  	      $scope.meetings.list = null;
  	    	$scope.meetings.message = "An error occurred with the GrappBox API. Please try again."
  	    	$scope.meetings.valid = false;
  	    	$scope.meetings.onLoad = false;
      	}
  		},
  		function onGetMeetingsFail(response) {
  			$scope.meetings.list = null;
  			$scope.meetings.onLoad = false;
  			$scope.meetings.valid = false;

        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "2.2.3":
            $rootScope.onUserTokenError()
            break;

            case "2.2.9":
          	$scope.meetings.message = "You don't have sufficent rights to perform this operation. Please try again."
            break;

            default:
          	$scope.meetings.message = "An error occurred. Please try again."
            break;
          }
        }
        else
        	$scope.meetings.message = "An error occurred with the GrappBox API. Please try again."
      }
    );
  };



  /* ==================== EXECUTION ==================== */

  _getTeamOccupation();
  _getNextMeetings();

}]);