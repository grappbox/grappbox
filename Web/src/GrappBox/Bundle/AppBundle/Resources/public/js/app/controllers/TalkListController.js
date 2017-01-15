/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP talk list
app.controller("TalkListController", ["accessFactory", "$http", "$q", "$rootScope", "$route", "$scope",
    function(accessFactory, $http, $q, $rootScope, $route, $scope) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.view = { loaded: false, valid: false };
	$scope.method = { formatObjectDate: "" };
  $scope.talk = { project_id: $route.current.params.project_id, team: {}, customer: {}, active: "team" };

  $scope.talk.team = { id: "", type: 2, loaded: false, valid: false, authorized: false, messages: null };
  $scope.talk.customer = { id: "", type: 1, loaded: false, valid: false, authorized: false, messages: null };



	/* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Reset talk list
  var _resetTalkList = function() {
    $scope.talk.team = { id: "", type: 2, loaded: false, valid: false, authorized: false, messages: null };
    $scope.talk.customer = { id: "", type: 1, loaded: false, valid: false, authorized: false, messages: null };
  };

  // Routine definition (local)
  // Get talk list
  var _getTalkList = function() {
  	var deferred = $q.defer();

  	$http.get($rootScope.api.url + "/timelines/" + $scope.talk.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function talkListReceived(response) {
  			if (response && response.data && response.data.info && response.data.info.return_code) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					angular.forEach((response.data && response.data.data && response.data.data.array ? response.data.data.array : null), function(value, key) {
  						if (value.typeId == "2")
  							$scope.talk.team = { id: value.id, type: 2, loaded: false, valid: false, authorized: false, messages: null };
  						else
  							$scope.talk.customer = { id: value.id, type: 1, loaded: false, valid: false, authorized: false, messages: null };
  					});
  					deferred.resolve();
  					break;

            // ISSUE #175 WORKAROUND
  					case "1.11.3":
            _resetTalkList();
            $scope.talk.team.loaded = true;
            $scope.talk.customer.loaded = true;
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
        else
          $rootScope.reject(true);
  			return deferred.promise;
  		},
  		function talkListNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
    			if (response.data.info.return_code == "11.1.3")
    				$rootScope.reject();
  				$scope.talk.team.messages = null;
  				$scope.talk.customer.messages = null;
    			deferred.reject();
        }
        else
          $rootScope.reject(true);

				return deferred.promise;
  		}
  	);
		return deferred.promise;
  };

  // Routine definition (local)
  // Get selected talk messages
  var _getTalks = function(talk) {
    talk.valid = false;
    talk.onLoad = true;

  	$http.get($rootScope.api.url + "/timeline/messages/" + talk.id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function talksReceived(response) {
  			if (response && response.data && response.data.info && response.data.info.return_code) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					talk.messages = (response.data && response.data.data && response.data.data.array ? response.data.data.array : null);
  					talk.valid = true;
  					talk.loaded = true;
            talk.authorized = true;
  					break;

  					case "1.11.3":
  					talk.messages = null;
  					talk.valid = true;
  					talk.loaded = true;
            talk.authorized = true;
						break;

  					default:
						talk.messages = null;
	  				talk.valid = false;
  					talk.loaded = true;
            talk.authorized = true;
  					break;
  				}
  			}
        else
          $rootScope.reject(true);
  		},
  		function talksNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "11.4.3":
            $rootScope.reject();
            break;

            case "11.4.9":
            talk.messages = null;
            talk.valid = true;
            talk.loaded = true;
            talk.authorized = false;
            break;

            default:
            talk.messages = null;
            talk.valid = false;
            talk.loaded = true;
            talk.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
  		}
		);
  };



  /* ==================== EXECUTION ==================== */

  accessFactory.projectAvailable();
  _resetTalkList();

  var talkList_promise = _getTalkList();
  talkList_promise.then(
  	function talkListPromiseSuccess() {
      $scope.talk.team.messages = _getTalks($scope.talk.team);
      $scope.talk.customer.messages = _getTalks($scope.talk.customer);
			$scope.view.valid = true;
			$scope.view.loaded = true;
  	},
  	function talkListPromiseFail() {
			$scope.view.valid = false;
			$scope.view.loaded = true;
  	}
  );

}]);
