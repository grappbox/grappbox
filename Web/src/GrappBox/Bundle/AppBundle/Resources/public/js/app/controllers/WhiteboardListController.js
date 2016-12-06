/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP whiteboard list
app.controller("WhiteboardListController", ["accessFactory", "$http", "notificationFactory", "$rootScope", "$route", "$scope", "$uibModal",
    function(accessFactory, $http, notificationFactory, $rootScope, $route, $scope, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { loaded: false, valid: false, authorized: false };
  $scope.whiteboards = { project_id: $route.current.params.project_id, list: "", add: "", delete: "", new: { name: "", error: "" } };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Get whiteboard list
  var _getWhiteboardList = function() {
    $http.get($rootScope.api.url + "/whiteboards/" + $scope.whiteboards.project_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function whiteboardListReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.whiteboards.list = (response.data.data && response.data.data.array ? response.data.data.array : null);
            $scope.view.valid = true;
            $scope.view.loaded = true;
            $scope.view.authorized = true;
            break;

            case "1.10.3":
            $scope.whiteboards.list = null;
            $scope.view.valid = true;
            $scope.view.loaded = true;
            $scope.view.authorized = true;
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.loaded = true;
            $scope.view.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      },
      function whiteboardListNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "10.1.3":
            $rootScope.reject();
            break;

            case "10.1.9":
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.loaded = true;
            $scope.view.authorized = false;
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.loaded = true;
            $scope.view.authorized = true;
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
  _getWhiteboardList();



  /* ==================== CREATE WHITEBOARD ==================== */

  // "Add whiteboard" button handler
  $scope.whiteboards.add = function() {
    $scope.whiteboards.new.name = "";

    var whiteboardCreation = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "whiteboardCreation.html", controller: "WhiteboardCreationController" });
    whiteboardCreation.result.then(
      function whiteboardCreationConfirmed() {
        $http.post($rootScope.api.url + "/whiteboard",
          { data: { projectId: $scope.whiteboards.project_id, whiteboardName: $scope.whiteboards.new.name }},
          { headers: { 'Authorization': $rootScope.user.token }}).then(
          function whiteboardCreated(response) {
            notificationFactory.success("Whiteboard created.");
            _getWhiteboardList();     
            $scope.whiteboards.new.name = "";
          },
          function whiteboardNotCreated(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "10.2.3":
                $rootScope.reject();
                break;

                case "10.2.9":
                notificationFactory.warning("You don\'t have permission to create a new whiteboard.");
                break;

                default:
                notificationFactory.error();
                break;
              }
            }
            else
              $rootScope.reject(true);
          }
        ),
        function whiteboardCreationCancelled() {
          $scope.whiteboards.new.name = "";
        }
      }
    );
  };



  /* ==================== DELETE WHITEBOARD ==================== */

  // "Delete whiteboard" button handler
  $scope.whiteboards.delete = function(whiteboard_data) {
    var whiteboardDeletion = $uibModal.open({ animation: true, size: "lg", backdrop: "static", templateUrl: "whiteboardDeletion.html", controller: "WhiteboardDeletionController" });
    whiteboardDeletion.result.then(
      function whiteboardDeletionConfirmed(data) {
        $http.delete($rootScope.api.url + "/whiteboard/" + whiteboard_data, { headers: { 'Authorization': $rootScope.user.token }}).then(
          function whiteboardDeleted(response) {
            notificationFactory.success("Whiteboard successfully deleted.");
            _getWhiteboardList();
          },
          function whiteboardNotDeleted(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "10.6.3":
                $rootScope.reject();
                break;

                case "10.6.9":
                notificationFactory.warning("You don\'t have permission to delete whiteboards.");
                break;

                default:
                notificationFactory.error();
                break;
              }
            }
            else
              $rootScope.reject(true);
          }
        ),
        function whiteboardDeletionCancelled() { }
      }
    );
  };

}]);



/**
* Controller definition (from view)
* Confirmation prompt for whiteboard creation.
*
*/
app.controller("WhiteboardCreationController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.whiteboardCreationConfirmed = function() {
    $scope.whiteboards.new.error = ($scope.whiteboards.new.name && $scope.whiteboards.new.name.length ? false : true);
    if (!$scope.whiteboards.new.error)
      $uibModalInstance.close();
  };
  $scope.whiteboardCreationCancelled = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* Confirmation prompt for whiteboard deletion.
*
*/
app.controller("WhiteboardDeletionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.whiteboardDeletionConfirmed = function() { $uibModalInstance.close(); };
  $scope.whiteboardDeletionCancelled = function() { $uibModalInstance.dismiss(); };
}]);