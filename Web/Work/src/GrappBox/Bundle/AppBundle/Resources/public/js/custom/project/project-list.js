/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP project page list
*
*/
app.controller('projectListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', '$route', '$location', function($rootScope, $scope, $routeParams, $http, $cookies, Notification, $route, $location) {

  var content = "";

  // Scope variables initialization
  $scope.data = { onLoad: true, userProjects: { }, isValid: false };

  // Get all user's current projects
  $http.get($rootScope.apiBaseURL + "/user/getprojects/" + $cookies.get("USERTOKEN"))
    .then(function userProjectsReceived(response) {
      $scope.data.userProjects = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
      $scope.data.isValid = true;
      $scope.data.onLoad = false;
    },
    function userProjectsNotReceived(response) {
      $scope.data.userProjects = null;
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
* APP project page access
*
*/

// Routine definition [3/3]
// Common behavior for isProjectAccessible
var isProjectAccessible_commonBehavior = function(deferred, $location) {
  deferred.reject();
  $location.path("project");
};

// Routine definition [2/3]
// Default behavior for isProjectAccessible
var isProjectAccessible_defaultBehavior = function(deferred, $location) {
  isProjectAccessible_commonBehavior(deferred, $location);
  Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
};

// Routine definition [1/3]
// Check if requested project is accessible
var isProjectAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  if ($route.current.params.id == 0)
  {
    deferred.resolve();
    return deferred.promise;
  }

  $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function successCallback(response) {
      deferred.resolve();
    },
    function errorCallback(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "6.3.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "6.3.4":
          isProjectAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "Project not found.", delay: 10000 });
          break;

          case "6.3.9":
          isProjectAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "You don\'t have access to the settings of this project.", delay: 10000 });
          break;

          default:
          isProjectAccessible_defaultBehavior(deferred, $location);
          break;
        }
      }
      else { isProjectAccessible_defaultBehaviorv(deferred, $location); }
    });

    return deferred.promise;
};

// "isProjectAccessible" routine injection
isProjectAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];
