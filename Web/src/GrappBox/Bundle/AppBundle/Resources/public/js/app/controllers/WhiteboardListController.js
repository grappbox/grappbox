/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard list
*
*/
app.controller("WhiteboardListController", ["$http", "notificationFactory", "$rootScope", "$route", "$scope", "$uibModal",
    function($http, notificationFactory, $rootScope, $route, $scope, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.method = { formatObjectDate: "" };

  $scope.whiteboards = { project_id: $route.current.params.project_id, list: "" };
  $scope.action = { addWhiteboard: "", deleteWhiteboard: "" };
  $scope.new = { name: "" };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Get whiteboard list
  var _getWhiteboardList = function() {
    $scope.view.valid = false;
    $scope.view.onLoad = true;

    $http.get($rootScope.api.url + "/whiteboards/" + $scope.whiteboards.project_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function onGetWhiteboardListSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.whiteboards.list = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
            $scope.view.valid = true;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;

            case "1.10.3":
            $scope.whiteboards.list = null;
            $scope.view.valid = true;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
        }
      },
      function onGetWhiteboardListFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "10.1.3":
            $rootScope.onUserTokenError();
            break;

            case "10.1.9":
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
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

  _getWhiteboardList();



  /* ==================== CREATE OBJECT (CREATE WHITEBOARD) ==================== */

  // "Add whiteboard" button handler
  $scope.action.onNewWhiteboard = function() {
    $scope.new.name = "";

    var modal_newWhiteboard = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_createNewWhiteboard.html", controller: "modal_createNewWhiteboard" });
    modal_newWhiteboard.result.then(
      function onModalConfirm() {
        $http.post($rootScope.api.url + "/whiteboard",
          { data: { projectId: $scope.whiteboards.project_id, whiteboardName: $scope.new.name }},
          { headers: { 'Authorization': $rootScope.user.token }}).then(
          function onPostWhiteboardSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.10.1")
              notificationFactory.error();
            else
              notificationFactory.success("Whiteboard successfully created.");
            _getWhiteboardList();     
          },
          function onPostWhiteboardFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "10.2.3":
                $rootScope.onUserTokenError();
                break;

                case "10.2.9":
                notificationFactory.warning("You don\'t have sufficient rights to perform this operation.");
                break;

                default:
                notificationFactory.error();
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };



  /* ==================== DELETE OBJECT (DELETE WHITEBOARD) ==================== */

  // "Delete whiteboard" button handler
  $scope.action.onDeleteWhiteboard = function(whiteboard_id) {
    var modal_deleteWhiteboard = $uibModal.open({ animation: true, size: "lg", backdrop: "static", templateUrl: "modal_deleteWhiteboard.html", controller: "modal_deleteWhiteboard" });
    modal_deleteWhiteboard.result.then(
      function onModalConfirm(data) {
        $http.delete($rootScope.api.url + "/whiteboard/" + whiteboard_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
          function onDeleteWhiteboardSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.10.1")
              notificationFactory.error();
            else
              notificationFactory.success("Whiteboard successfully deleted.");
            _getWhiteboardList();
          },
          function onDeleteWhiteboardFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "10.6.3":
                $rootScope.onUserTokenError();
                break;

                case "10.6.9":
                notificationFactory.warning("You don\'t have sufficient rights to perform this operation.");
                break;

                default:
                notificationFactory.error();
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };

}]);



/**
* Controller definition (from view)
* WHITEBOARD CREATION => new message form.
*
*/
app.controller("modal_createNewWhiteboard", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { name: false };

  $scope.modal_confirmWhiteboardCreation = function() {
    $scope.error.name = ($scope.new.name && $scope.new.name.length ? false : true);
    if (!$scope.error.name)
      $uibModalInstance.close();
  };
  $scope.modal_cancelWhiteboardCreation = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* WHITEBOARD DELETION => confirmation prompt.
*
*/
app.controller("modal_deleteWhiteboard", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_confirmWhiteboardDeletion = function() { $uibModalInstance.close(); };
  $scope.modal_cancelWhiteboardDeletion = function() { $uibModalInstance.dismiss(); };
}]);