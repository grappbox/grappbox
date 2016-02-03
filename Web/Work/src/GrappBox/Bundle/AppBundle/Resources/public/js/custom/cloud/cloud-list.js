/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP Cloud list page (one per project)
*
*/
app.controller("cloudListController", ["$scope", "$http", "$rootScope", "$cookies", function($scope, $http, $rootScope, $cookies) {

  // Scope variables initialization
  $scope.data = { onLoad: true, userProjects: "", isValid: false };

  // Get all user"s current projects (with information)
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

}]);


// Routine definition [3/3]
// Common behavior for isCloudAccessible
var isCloudAccessible_commonBehavior = function(deferred, $location) {
  deferred.reject();
  $location.path("cloud");
};

// Routine definition [2/3]
// Default behavior for isCloudAccessible
var isCloudAccessible_defaultBehavior = function(deferred, $location) {
  isCloudAccessible_commonBehavior(deferred, $location);
  Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
};

// Routine definition [1/3]
// Check if requested Cloud is accessible
var isCloudAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  $http.get($rootScope.apiBaseURL + "/projects/getinformations/" + $cookies.get("USERTOKEN") + "/" + $route.current.params.id)
  .then(function projectInformationsReceived(response) {
    deferred.resolve();
  },
  function projectInformationsNotReceived(response) {
    if (response.data.info.return_code) {
      switch(response.data.info.return_code) {
        case "6.3.3":
        deferred.reject();
        $rootScope.onUserTokenError();
        break;

        case "6.3.4":
        isCloudAccessible_commonBehavior(deferred, $location);
        Notification.warning({ message: "Project not found. Please try again.", delay: 10000 });
        break;

        case "6.3.9":
        isCloudAccessible_commonBehavior(deferred, $location);
        Notification.warning({ message: "You don\'t have access to this project. Please try again.", delay: 10000 });
        break;

        default:
        isCloudAccessible_defaultBehavior(deferred, $location);
        break;
      }
    }
    else { isCloudAccessible_defaultBehavior(deferred, $location); }
  });

  return deferred.promise;
};

// "isCloudAccessible" routine injection
isCloudAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];