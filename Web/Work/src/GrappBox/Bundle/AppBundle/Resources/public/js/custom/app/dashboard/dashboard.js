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
app.controller("dashboardController", ["$rootScope", "$scope", "localStorageService", "$base64", "$route", "$http", function($rootScope, $scope, localStorageService, $base64, $route, $http) {

	// Scope variables initialization
	$scope.occupation = { list: "", onLoad: true, valid: false, message: "" };
	$scope.meetings = { list: "", onLoad: true, valid: false, message: "" };

	$scope.content = { projectID: "" };
	$scope.content.projectID = $route.current.params.project_id;

  // Local storage initialization
  if (!localStorageService.get("HAS_PROJECT")) {
    $http.get($rootScope.api.url + "/projects/getinformations/" + $rootScope.user.token + "/" + $scope.content.projectID)
    .then(function onGetSuccess(response) {
      var data = (response.data && Object.keys(response.data.data).length ? response.data.data : null);

      localStorageService.set("HAS_PROJECT", true);
      localStorageService.set("PROJECT_ID", $base64.encode($scope.content.projectID));
      localStorageService.set("PROJECT_NAME", $base64.encode(data.name));

      $rootScope.project.id = $scope.content.projectID;
      $rootScope.project.name = data.name;
      $rootScope.project.set = true;
    },
    function onGetFail(response) {
      context.rootScope.onUserTokenError();
    });
  }

	/* ==================== INITIALIZATION (TEAM OCCUPATION) ==================== */

	// Get current team occupation
	$http.get($rootScope.api.url + "/dashboard/getteamoccupation/" + $rootScope.user.token + "/" + $scope.content.projectID)
		.then(function onGetSuccess(response) {

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
		function onGetFail(response) {
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
    });



	/* ==================== INITIALIZATION (NEXT MEETINGS) ==================== */

	// Routine definition
	// Format date
	$scope.formatObjectDate = function(dateToFormat) {
		return dateToFormat.substring(0, dateToFormat.lastIndexOf(":"));
	};

	// Get next meetings
	$http.get($rootScope.api.url + "/dashboard/getnextmeetings/" + $rootScope.user.token + "/" + $scope.content.projectID)
		.then(function onGetSuccess(response) {

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
		function onGetFail(response) {
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
    });

}]);