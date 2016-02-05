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
app.controller("whiteboardListController", ["$scope", "$rootScope", "$http", "$cookies", function($scope, $rootScope, $http, $cookies) {

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
var isWhiteboardAccessible_defaultBehavior = function(deferred, $location) {
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
    if (response.data.info.return_code) {
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
        isWhiteboardAccessible_defaultBehavior(deferred, $location);
        break;
      }
    }
    else { isWhiteboardAccessible_defaultBehavior(deferred, $location); }
  });

  return deferred.promise;
};

// "isWhiteboardAccessible" routine injection
isWhiteboardAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];