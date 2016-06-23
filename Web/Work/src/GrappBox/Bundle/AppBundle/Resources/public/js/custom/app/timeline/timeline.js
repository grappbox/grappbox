/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP timeline
*
*/
app.controller("timelineController", ["$rootScope", "$scope", "$route", "$http", "$q", function($rootScope, $scope, $route, $http, $q) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.content = { onLoad: true, valid: false, message: "" };
	$scope.method = { switchTab: "", formatObjectDate: "" };
  $scope.timeline = { project_id: $route.current.params.project_id, team: {}, customer: {} };



	/* ==================== ROUTINES ==================== */

  // Routine definition
  // Get timeline list
  var getTimelineList = function() {
  	var deferred = $q.defer();

  	$http.get($rootScope.api.url + "/timeline/gettimelines/" + $rootScope.user.token + "/" + $scope.timeline.project_id).then(
  		function onGetSuccess(response) {
  			if (response.data.info) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					angular.forEach((response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null), function(value, key) {
  						if (value.typeId == "2")
  							$scope.timeline.team = { id: value.id, typeId: value.typeId, active: true, onLoad: true, valid: false, messages: null };
  						else
  							$scope.timeline.customer = { id: value.id, typeId: value.typeId, active: false, onLoad: true, valid: false, messages: null };
  					});
  					deferred.resolve();
  					break;

  					case "1.11.3":
  					$scope.timeline.team = null;
  					$scope.timeline.customer = null;
  					$scope.content.message = "This project doesn't include any timeline."
  					deferred.resolve();
  					break;

  					default:
  					$scope.timeline.team = null;
  					$scope.timeline.customer = null;
  					$scope.content.message = "An error occurred. Please try again."
  					deferred.reject();
  					break;
  				}
  			}
  			else {
					$scope.timeline.team = null;
					$scope.timeline.customer = null;
  				$scope.content.message = "An error occurred with the GrappBox API. Please try again."
  				deferred.reject();
  			}
  			return deferred.promise;
  		},
  		function onGetFail(response) {
  			if (response.data.info && response.data.info.return_code == "11.1.3")
  				$rootScope.onUserTokenError();
				$scope.timeline.team = null;
				$scope.timeline.customer = null;
  			deferred.reject();

				return deferred.promise;
  		}
  	);
		return deferred.promise;
  };

  // Routine definition
  // Get selected timeline messages
  var getTimelineMessages = function(timeline) {
  	$http.get($rootScope.api.url + "/timeline/getmessages/" + $rootScope.user.token + "/" + timeline.id).then(
  		function onGetSuccess(response) {
  			if (response.data.info) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					timeline.messages = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
  					timeline.valid = true;
  					timeline.onLoad = false;
  					break;

  					case "1.11.3":
  					timeline.messages = null;
  					timeline.valid = true;
  					timeline.onLoad = false;
						break;

  					default:
						timeline.messages = null;
	  				timeline.valid = false;
  					timeline.onLoad = false;
  					break;
  				}
  			}
  			else {
					timeline.messages = null;
  				timeline.valid = false;
					timeline.onLoad = false;
  			}
  		},
  		function onGetFail(response) {
  			if (response.data.info && response.data.info.return_code == "11.4.9")
  				$rootScope.onUserTokenError();
				timeline.messages = null;
				timeline.valid = false;
				timeline.onLoad = false;
  		}
		);
  };

	// Routine definition
  // Switch tab (timeline)
  $scope.method.switchTab = function(name) {
  	$scope.timeline.team.active = (name == "team" ? true : false);
  	$scope.timeline.customer.active = (name == "customer" ? true : false);
  };

  // Routine definition
  // Format object date (posted)
  $scope.method.formatObjectDate = function(dateToFormat) {
  	return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };



  /* ==================== EXECUTION ==================== */

  var timelineList_promise = getTimelineList();
  timelineList_promise.then(
  	function onGetSuccess() {
  		$scope.timeline.team.messages = getTimelineMessages($scope.timeline.team);
  		$scope.timeline.customer.messages = getTimelineMessages($scope.timeline.customer);
			$scope.content.valid = true;
			$scope.content.onLoad = false;
  	},
  	function onGetFail() {
			$scope.content.valid = false;
			$scope.content.onLoad = false;
  	}
  );

}]);