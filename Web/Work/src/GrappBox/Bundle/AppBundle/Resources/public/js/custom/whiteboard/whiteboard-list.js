/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard list page (several per project)
*
*/
app.controller("whiteboardListController", ["$scope", "$rootScope", "$http", "$cookies", "$uibModal", "Notification", function($scope, $rootScope, $http, $cookies, $uibModal, Notification) {

  var context = "";

  // Scope variables initialization
  $scope.data = { onLoad: true, projects: { }, isValid: false }

  // Get all user's current project(s)
  $http.get($rootScope.apiBaseURL + "/user/getprojects/" + $cookies.get("USERTOKEN"))
  .then(function projectsReceived(response) {
    $scope.data.projects = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
    $scope.data.isValid = true;
    $scope.data.onLoad = false;

    // Get current projet whiteboard(s)
    $scope.data.projectsWhiteboards_onLoad = {};
    $scope.data.projectsWhiteboards_content = {};
    $scope.data.projectsWhiteboards_message = {};

    context = {"scope": $scope, "rootScope": $rootScope, "cookies": $cookies};
    angular.forEach($scope.data.projects, function(project) {
      context.scope.data.projectsWhiteboards_onLoad[project.name] = true;

      $http.get(context.rootScope.apiBaseURL + "/whiteboard/list/" + context.cookies.get("USERTOKEN") + "/" + project.id)
      .then(function projectWhiteboardsReceived(response) {
        context.scope.data.projectsWhiteboards_onLoad[project.name] = false;
        context.scope.data.projectsWhiteboards_content[project.name] = (response.data && Object.keys(response.data.data).length ? response.data.data : null);
        context.scope.data.projectsWhiteboards_message[project.name] = (response.data.info && response.data.info.return_code == "1.10.1" ? "_valid" : "_empty");
      },
      function projectWhiteboardsNotReceived(response) {
        context.scope.data.projectsWhiteboards_onLoad[project.name] = false;
        context.scope.data.projectsWhiteboards_content[project.name] = null;
        context.scope.data.projectsWhiteboards_message[project.name] = "_invalid";

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "10.1.3":
            context.rootScope.onUserTokenError();
            break;

            case "10.1.9":
            context.scope.data.projectsWhiteboards_message[project.name] = "_denied";
            break;

            default:
            context.scope.data.projectsWhiteboards_message[project.name] = "_invalid";
            break;
          }
        });
    }, context);
  },
  function userProjectsNotReceived(response) {
    $scope.data.projects = null;
    $scope.data.isValid = false;
    $scope.data.onLoad = false;
  });

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };



  /* ==================== DELETE OBJECT (WHITEBOARD) ==================== */

  $scope.view_deleteWhiteboard = function($event) {
    var modalInstance_askForDeletionConfirmation = "";
    var whiteboardName = "";
    var whiteboardID = "";
    var projectName = "";
    var projectID = "";

    whiteboardID = angular.element($event.currentTarget).attr("data-id");
    whiteboardName = angular.element($event.currentTarget).attr("data-name");
    projectName = angular.element($event.currentTarget).attr("data-project-name");
    projectID = angular.element($event.currentTarget).attr("data-project-id");
    modalInstance_askForDeletionConfirmation = $uibModal.open({ animation: true, templateUrl: "view_deleteWhiteboard.html", controller: "view_deleteWhiteboard" });
    modalInstance_askForDeletionConfirmation.result.then(function deletionConfirmed(response) {
      $http.delete($rootScope.apiBaseURL + "/whiteboard/delete/" + $cookies.get("USERTOKEN") + "/" + whiteboardID)
      .then(function deletionConfirmationReceived(response) {
        Notification.success({ message: "Deleted: \"" + whiteboardName + "\"", delay: 5000 });

        $scope.data.projectsWhiteboards_onLoad[projectName] = true;
        $http.get($rootScope.apiBaseURL + "/whiteboard/list/" + $cookies.get("USERTOKEN") + "/" + projectID)
        .then(function projectWhiteboardsReceived(response) {
          $scope.data.projectsWhiteboards_onLoad[projectName] = false;
          $scope.data.projectsWhiteboards_content[projectName] = (response.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.data.projectsWhiteboards_message[projectName] = (response.data.info && response.data.info.return_code == "1.10.1" ? "_valid" : "_empty");
        },
        function projectWhiteboardsNotReceived(response) {
          $scope.data.projectsWhiteboards_onLoad[projectName] = false;
          $scope.data.projectsWhiteboards_content[projectName] = null;
          $scope.data.projectsWhiteboards_message[projectName] = "_invalid";

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "10.1.3":
              $rootScope.onUserTokenError();
              break;

              case "10.1.9":
              $scope.data.projectsWhiteboards_message[projectName] = "_denied";
              break;

              default:
              $scope.data.projectsWhiteboards_message[projectName] = "_invalid";
              break;
            }
          });
      },
      function deletionConfirmationNotReceived(response) {
        if (response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "10.6.3":
            context.rootScope.onUserTokenError();
            break;

            case "10.6.9":
            Notification.warning({ message: "You don't have permission to delete this whiteboard. Please try again.", delay: 5000 });
            break;

            default:
            Notification.warning({ message: "This whiteboard doesn't exist. Please try again.", delay: 5000 });
            break;
          }
        }
        else
          Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
      });
    },
    function deletionNotConfirmed() { Notification.warning({ message: "Deletion cancelled.", delay: 5000 }); });
  };

}]);



/**
* Routine definition
* APP whiteboard page access
*
*/

// Routine definition [3/3]
// Common behavior for isWhiteboardAccessible
var isWhiteboardAccessible_commonBehavior = function(deferred, $location) {
  deferred.reject();
  $location.path("whiteboard");
};

// Routine definition [2/3]
// Default behavior for isWhiteboardAccessible
var isWhiteboardAccessible_defaultBehavior = function(deferred, $location, Notification) {
  isWhiteboardAccessible_commonBehavior(deferred, $location);
  Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
};

// Routine definition [1/3]
// Check if requested Whiteboard is accessible
var isWhiteboardAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  $http.get($rootScope.apiBaseURL + "/whiteboard/open/" + $cookies.get("USERTOKEN") + "/" + $route.current.params.id)
  .then(function projectInformationsReceived(response) {
    deferred.resolve();
  },
  function projectInformationsNotReceived(response) {
    if (response.data.info && response.data.info.return_code) {
      switch(response.data.info.return_code) {
        case "10.3.3":
        deferred.reject();
        $rootScope.onUserTokenError();
        break;

        case "10.3.4":
        isWhiteboardAccessible_commonBehavior(deferred, $location);
        Notification.warning({ message: "Whiteboard not found. Please try again.", delay: 10000 });
        break;

        case "10.3.9":
        isWhiteboardAccessible_commonBehavior(deferred, $location);
        Notification.warning({ message: "You don\'t have access to this whiteboard. Please try again.", delay: 10000 });
        break;

        default:
        isWhiteboardAccessible_defaultBehavior(deferred, $location, Notification);
        break;
      }
    }
    else { isWhiteboardAccessible_defaultBehavior(deferred, $location, Notification); }
  });

  return deferred.promise;
};

// "isWhiteboardAccessible" routine injection
isWhiteboardAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];



/**
* Service definition (global)
* Get data from an HTML selector, and pass it to a modal instance.
*
*/
app.factory("modalInputService", function() {
  return {
    getData: function(dataSelector, modalInstance) {
      var data = "";

      data = angular.element(document.querySelector(dataSelector));
      angular.element(data).removeAttr("class");
      if (data && data.val() !== "")
        modalInstance.close(data.val());
      else
        angular.element(data).attr("class", "input-error");
    }
  };
});



/**
* Controller definition (from view)
* WHITEBOARD CREATION => set whiteboard name.
*
*/
app.controller("view_createWhiteboard", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  $scope.view_createWhiteboardSuccess = function() { modalInputService.getData("#view_newWhiteboardName", $uibModalInstance); };
  $scope.view_createWhiteboardFailure = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* WHITEBOARD DELETION => is the action confirmed?
*
*/
app.controller("view_deleteWhiteboard", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_deleteWhiteboardActionConfirmed = function() { $uibModalInstance.close(); };
  $scope.view_deleteWhiteboardActionCancelled = function() { $uibModalInstance.dismiss(); };
}]);