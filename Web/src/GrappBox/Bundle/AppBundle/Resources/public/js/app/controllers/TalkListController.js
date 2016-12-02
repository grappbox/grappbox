/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP talk list
app.controller("TalkListController", ["accessFactory", "$http", "$location", "notificationFactory", "$q", "$rootScope", "$route", "$scope", "talkFactory", "$uibModal",
    function(accessFactory, $http, $location, notificationFactory, $q, $rootScope, $route, $scope, talkFactory, $uibModal) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.view = { loaded: false, valid: false, authorized: false };
	$scope.method = { formatObjectDate: "" };
  $scope.talk = { project_id: $route.current.params.project_id, team: {}, customer: {} };



	/* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Reset talk list
  var _resetTalkList = function() {
    $scope.talk.team = { id: "", loaded: false, valid: false, messages: null };
    $scope.talk.customer = { id: "", loaded: false, valid: false, messages: null };
  };

  // Routine definition (local)
  // Get talk list
  var _getTalkList = function() {
  	var deferred = $q.defer();

  	$http.get($rootScope.api.url + "/timelines/" + $scope.talk.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function onGetTalkListSucces(response) {
  			if (response && response.data && response.data.info && response.data.info.return_code) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					angular.forEach((response.data && response.data.data && response.data.data.array ? response.data.data.array : null), function(value, key) {
  						if (value.typeId == "2")
  							$scope.talk.team = { id: value.id, loaded: false, valid: false, messages: null };
  						else
  							$scope.talk.customer = { id: value.id, loaded: false, valid: false, messages: null };
  					});
            $scope.view.authorized = true;
  					deferred.resolve();
  					break;

            // ISSUE #175 WORKAROUND
  					case "1.11.3":
            _resetTalkList();
            $scope.talk.team.loaded = true;
            $scope.talk.customer.loaded = true;
            $scope.view.authorized = false;
  					deferred.reject();
  					break;

  					default:
            _resetTalkList();
            $scope.talk.team.loaded = true;
            $scope.talk.customer.loaded = true;
  					deferred.reject();
  					break;
  				}
  			}
  			else {
          _resetTalkList();
  				deferred.reject();
  			}
  			return deferred.promise;
  		},
  		function onGetTalkListFail(response) {
  			if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code == "11.1.3")
  				$rootScope.reject();
				$scope.talk.team = null;
				$scope.talk.customer = null;
  			deferred.reject();

				return deferred.promise;
  		}
  	);
		return deferred.promise;
  };

  // Routine definition (local)
  // Get selected talk messages
  var _getTalkMessages = function(talk) {
    talk.valid = false;
    talk.onLoad = true;

  	$http.get($rootScope.api.url + "/timeline/messages/" + talk.id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function onGetTalkMessagesSuccess(response) {
  			if (response && response.data && response.data.info && response.data.info.return_code) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					talk.messages = (response.data && response.data.data && response.data.data.array ? response.data.data.array : null);
  					talk.valid = true;
  					talk.loaded = true;
            $scope.view.authorized = true;
  					break;

  					case "1.11.3":
  					talk.messages = null;
  					talk.valid = true;
  					talk.loaded = true;
            $scope.view.authorized = true;
						break;

  					default:
						talk.messages = null;
	  				talk.valid = false;
  					talk.loaded = true;
  					break;
  				}
  			}
  			else {
					talk.messages = null;
  				talk.valid = false;
					talk.loaded = true;
  			}
  		},
  		function onGetTalkMessagesFail(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "11.4.3":
            $rootScope.reject();
            break;

            case "11.4.9":
            talk.messages = null;
            talk.valid = false;
            talk.loaded = true;
            $scope.view.authorized = false;
            break;

            default:
            talk.messages = null;
            talk.valid = false;
            talk.loaded = true;
            break;
          }
        }
        else {
          talk.messages = null;
          talk.valid = false;
          talk.loaded = true;
        }
  		}
		);
  };



  /* ==================== SCOPE ROUTINES ==================== */

  // Routine definition (scope)
  // Format object date
  $scope.method.formatObjectDate = function(dateToFormat) {
  	return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };



  /* ==================== EXECUTION ==================== */

  accessFactory.projectAvailable();
  _resetTalkList();
  
  var talkList_promise = _getTalkList();
  talkList_promise.then(
  	function onPromiseGetSuccess() {
      $scope.talk.team.messages = _getTalkMessages($scope.talk.team);
      $scope.talk.customer.messages = _getTalkMessages($scope.talk.customer);
			$scope.view.valid = true;
			$scope.view.loaded = true;
  	},
  	function onPromiseGetFail() {
			$scope.view.valid = false;
			$scope.view.loaded = true;
  	}
  );

}]);