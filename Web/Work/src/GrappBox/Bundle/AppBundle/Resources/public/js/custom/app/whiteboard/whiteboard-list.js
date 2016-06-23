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
app.controller("whiteboardListController", ["$scope", "$rootScope", "$http", "$uibModal", "Notification", function($scope, $rootScope, $http, $uibModal, Notification) {

  /* ==================== INITIALIZATION ==================== */

  var context = "";

  // Scope variables initialization
  $scope.data = { onLoad: true, projects: {}, isValid: false };

  // START
  // Get all user's current project(s)
  $http.get($rootScope.api.url + "/user/getprojects/" + $rootScope.user.token)
  .then(function projectsReceived(response) {
    $scope.data.projects = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
    $scope.data.isValid = true;
    $scope.data.onLoad = false;

    // Get current projet whiteboard(s)
    $scope.data.projectsWhiteboards_onLoad = {};
    $scope.data.projectsWhiteboards_content = {};
    $scope.data.projectsWhiteboards_message = {};

    context = { "scope": $scope, "rootScope": $rootScope };
    angular.forEach($scope.data.projects, function(project) {
      context.scope.data.projectsWhiteboards_onLoad[project.name] = true;

      $http.get(context.rootScope.api.url + "/whiteboard/list/" + context.rootScope.user.token + "/" + project.id)
      .then(function projectWhiteboardsReceived(response) {
        context.scope.data.projectsWhiteboards_onLoad[project.name] = false;
        context.scope.data.projectsWhiteboards_content[project.name] = (response.data && response.data.data ? (Object.keys(response.data.data.array).length ? response.data.data.array : null) : null);
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

  // Routine definition
  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  // Routine definition
  // Refresh whiteboard list for given project
  var refreshProjectWhiteboardList = function(projectID, projectName) {
    $scope.data.projectsWhiteboards_onLoad[projectName] = true;
    $http.get($rootScope.api.url + "/whiteboard/list/" + $rootScope.user.token + "/" + projectID)
      .then(function projectWhiteboardsReceived(response) {
        $scope.data.projectsWhiteboards_onLoad[projectName] = false;
        $scope.data.projectsWhiteboards_content[projectName] = (response.data && response.data.data ? (Object.keys(response.data.data.array).length ? response.data.data.array : null) : null);
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
  };  



  /* ==================== ADD OBJECT (WHITEBOARD) ==================== */

  // "Add" button handler (whiteboard)
  $scope.view_addWhiteboard = function($event) {
    var modalInstance_askForFolderName = "";
    var projectID = "";
    var projectName = "";

    projectID = angular.element($event.currentTarget).attr("data-project-id");    
    projectName = angular.element($event.currentTarget).attr("data-project-name");
    modalInstance_askForFolderName = $uibModal.open({ animation: true, templateUrl: "view_createWhiteboard.html", controller: "view_createWhiteboard" });
    modalInstance_askForFolderName.result.then(function creationConfirmed(response) {
      Notification.info({ message: "Loading...", delay: 5000 });
      $http.post($rootScope.api.url + "/whiteboard/new", {
        data: {
          token: $rootScope.user.token,
          projectId: projectID,
          whiteboardName: response
      }})
      .then(function creationConfirmationReceived(response) {
        Notification.success({ message: "Whiteboard created.", delay: 5000 });
        refreshProjectWhiteboardList(projectID, projectName);
      },
      function creationConfirmationNotReceived(response) {
        if (response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "10.2.3":
            context.rootScope.onUserTokenError();
            break;

            case "10.2.9":
            Notification.warning({ message: "You don't have permission to create whiteboards on this project. Please try again.", delay: 5000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
            break;
          }
        }
        else
          Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
      });
    },
    function creationNotConfirmed() { });
  };



  /* ==================== DELETE OBJECT (WHITEBOARD) ==================== */

  // "Delete" button handler (whiteboard)
  $scope.view_deleteWhiteboard = function($event) {
    var modalInstance_askForDeletionConfirmation = "";
    var projectID = "";
    var projectName = "";
    var whiteboardID = "";
    var whiteboardName = "";

    projectID =  angular.element($event.currentTarget).attr("data-project-id");
    projectName = angular.element($event.currentTarget).attr("data-project-name");
    whiteboardID = angular.element($event.currentTarget).attr("data-id");
    whiteboardName = angular.element($event.currentTarget).attr("data-name");
    modalInstance_askForDeletionConfirmation = $uibModal.open({ animation: true, templateUrl: "view_deleteWhiteboard.html", controller: "view_deleteWhiteboard" });
    modalInstance_askForDeletionConfirmation.result.then(function deletionConfirmed(response) {
      Notification.info({ message: "Loading...", delay: 5000 });
      $http.delete($rootScope.api.url + "/whiteboard/delete/" + $rootScope.user.token + "/" + whiteboardID)
      .then(function deletionConfirmationReceived(response) {
        Notification.success({ message: "Deleted: \"" + whiteboardName + "\"", delay: 5000 });
        refreshProjectWhiteboardList(projectID, projectName);
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
            Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
            break;
          }
        }
        else
          Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
      });
    },
    function deletionNotConfirmed() { });
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
var isWhiteboardAccessible = function($q, $http, $rootScope, $route, $location, Notification) {
  var deferred = $q.defer();

  $http.get($rootScope.api.url + "/whiteboard/open/" + $rootScope.user.token + "/" + $route.current.params.id)
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
isWhiteboardAccessible["$inject"] = ["$q", "$http", "$rootScope", "$route", "$location", "Notification"];



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