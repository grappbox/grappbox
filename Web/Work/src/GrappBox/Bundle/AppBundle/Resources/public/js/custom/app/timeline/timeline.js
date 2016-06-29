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
app.controller("timelineController", ["$rootScope", "$scope", "$route", "$http", "$q", "$uibModal", "Notification", function($rootScope, $scope, $route, $http, $q, $uibModal, Notification) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.content = { onLoad: true, valid: false, message: "" };
	$scope.method = { switchTab: "", formatObjectDate: "" };

  $scope.timeline = { project_id: $route.current.params.project_id, team: {}, customer: {} };
  $scope.message = { title: "", content: "" };


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
    timeline.valid = false;
    timeline.onLoad = true;

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



  /* ==================== CREATE OBJECT (CREATE MESSAGE) ==================== */

  // "Add message" button handler
  $scope.view_onNewMessage = function() {
    var modalInstance_newMessage = "";

    modalInstance_newMessage = $uibModal.open({ animation: true, size: "lg", templateUrl: "view_createNewMessage.html", controller: "view_createNewMessage" });
    modalInstance_newMessage.result.then(
      function onModalConfirm(data) {
        $http.post($rootScope.api.url + "/timeline/postmessage/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id),
        { data: { token: $rootScope.user.token, title: data.title, message: data.content }}).then(
          function onPostSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.warning({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            Notification.success({ title: "Timeline", message: "Message successfully posted.", delay: 2000 });
            if ($scope.timeline.team.active)
              $scope.timeline.team.messages = getTimelineMessages($scope.timeline.team);
            else
              $scope.timeline.customer.messages = getTimelineMessages($scope.timeline.customer);       
          },
          function onPostFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.2.3":
                $rootScope.onUserTokenError();
                break;

                case "11.2.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };



  /* ==================== DELETE OBJECT (DELETE MESSAGE) ==================== */

  // "Delete message" button handler
  $scope.view_onMessageDelete = function(message_id) {
    var modalInstance_deleteMessage = "";

    modalInstance_deleteMessage = $uibModal.open({ animation: true, size: "lg", templateUrl: "view_deleteMessage.html", controller: "view_deleteMessage" });
    modalInstance_deleteMessage.result.then(
      function onModalConfirm(data) {
        $http.delete($rootScope.api.url + "/timeline/archivemessage/" + $rootScope.user.token + "/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id) + "/" + message_id).then(
          function onDeleteSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.warning({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            Notification.success({ title: "Timeline", message: "Message successfully deleted.", delay: 2000 });
            if ($scope.timeline.team.active)
              $scope.timeline.team.messages = getTimelineMessages($scope.timeline.team);
            else
              $scope.timeline.customer.messages = getTimelineMessages($scope.timeline.customer);       
          },
          function onDeleteFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.6.3":
                $rootScope.onUserTokenError();
                break;

                case "11.6.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                case "11.6.4":
                Notification.error({ title: "Timeline", message: "This message does not exist.", delay: 3000 });
                break;

                default:
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };

}]);



/**
* Controller definition (from view)
* MESSAGE CREATION => new message form.
*
*/
app.controller("view_createNewMessage", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  $scope.message = { title: "", content: "" };
  $scope.error = { title: false, content: false };

  $scope.view_confirmMessageCreation = function() {
    $scope.error.title = ($scope.message.title && $scope.message.title.length ? false : true);
    $scope.error.content = ($scope.message.content && $scope.message.content.length ? false : true);

    if (!$scope.error.title && !$scope.error.content)
      $uibModalInstance.close($scope.message);
  };
  $scope.view_cancelMessageCreation = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* MESSAGE DELETION => confirmation prompt.
*
*/
app.controller("view_deleteMessage", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  $scope.view_confirmMessageDeletion = function() { $uibModalInstance.close(); };
  $scope.view_cancelMessageDeletion = function() { $uibModalInstance.dismiss(); };
}]);