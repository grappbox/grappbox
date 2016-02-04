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
app.controller("whiteboardListController", ["$scope", "$routeParams", "$http", function($scope, $routeParams, $http) {
  $http.get("../resources/_temp/whiteboards.json").success(function(data) { $scope.whiteboardListContent = data; });
}]);


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