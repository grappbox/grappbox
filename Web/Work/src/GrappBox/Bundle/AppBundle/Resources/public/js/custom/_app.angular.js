/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP and main controller definition
*
*/
var app = angular.module("grappbox", ["ngRoute", "ngCookies", "ngAnimate", 'mwl.calendar', "ui.bootstrap", "panhandler", "ui-notification", "naif.base64", "ngTagsInput"]);

/* ==================== INITIALIZATION ==================== */

// TWIG template conflict fix
app.config(["$interpolateProvider", function($interpolateProvider) {
  $interpolateProvider.startSymbol("{[{").endSymbol("}]}");
}]);

// Cross-domain URLs calls fix
app.config(["$httpProvider", function($httpProvider) {
  $httpProvider.useApplyAsync(true);
  $httpProvider.defaults.useXDomain = true;
  delete $httpProvider.defaults.headers.common["X-Requested-With"];
}]);

// Bootstrap notifications settings
app.config(["NotificationProvider", function(NotificationProvider) {
  NotificationProvider.setOptions({
    startTop: 20,
    startRight: 10,
    verticalSpacing: 20,
    horizontalSpacing: 20,
    positionX: "right",
    positionY: "top"
  });
}]);

// Controller definition
// GrappBox (main)
app.controller("grappboxController", ["$scope", "$location", function($scope, $location) {

  $scope.routeActiveList  = { "/": false, "/bugtracker": false, "/calendar": false, "/cloud": false, "/notifications": false, "/profile": false, "/project": false, "/timeline": false, "/whiteboard": false };
  $scope.routeIconList    = { "/": "fa-bolt", "/bugtracker": "fa-code", "/calendar": "fa-calendar", "/cloud": "fa-upload", "/notifications": "fa-exclamation", '/profile': "fa-sliders", "/project": "fa-folder", "/timeline": "fa-sort-amount-asc", "/whiteboard": "fa-pencil" };
  $scope.routeCurrentIcon = "";

  // Routine definition
  // Check if the request route is not a subsection of another one
  var isSubRouteOf = function(routeToTest, newRoute) {
    var isRouteKnown = false;

    if (newRoute.indexOf(routeToTest) > -1) { 
      $scope.routeActiveList["/" + routeToTest] = true;
      $scope.routeCurrentIcon = $scope.routeIconList["/" + routeToTest];
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // On route change (start)
  $scope.$on("$routeChangeStart", function() {
    var newRoute = $location.path();
    var isRouteKnown = false;

    $scope.routeCurrentIcon = "";
    angular.forEach($scope.routeActiveList, function(isCurrentPathActive, currentPath) {
      if (currentPath === newRoute) {
        isRouteKnown = true;
        $scope.routeActiveList[currentPath] = true;
        $scope.routeCurrentIcon = $scope.routeIconList[currentPath];
      }
      else
        $scope.routeActiveList[currentPath] = false;
    });
    if (!isRouteKnown)
      isRouteKnown = (isSubRouteOf("bugtracker", newRoute) ? true : (isSubRouteOf("cloud", newRoute) ? true : (isSubRouteOf("whiteboard", newRoute) ? true : false)));
    if (!isRouteKnown)
      $scope.routeCurrentIcon = $scope.routeIconList["error"];
  });

}]);



/**
* ROOTSCOPE definition
* "layout, loading, apiVersion, apiBaseURL"
*
*/
app.run(["$rootScope", "$location", "$cookies", "$http", "$window", function($rootScope, $location, $cookies, $http, $window) {

  // ROOTSCOPE variables
  $rootScope.apiVersion = "V0.2"
  $rootScope.apiBaseURL = "http://api.grappbox.com/app_dev.php/" + $rootScope.apiVersion;

  $rootScope.onLoad = false;

  // ROOTSCOPE routine definition
  // Clear cookies and redirect user to login (with error)
  $rootScope.onUserTokenError = function() {
    $cookies.put("LASTLOGINMESSAGE", sha512("_DENIED"), { path: "/" });
    $cookies.remove("USERTOKEN", { path: "/" });
    $cookies.remove("CLOUDSAFE", { path: "/" });
    $window.location.href = "/#login";
  };

  // On route change (start)
  $rootScope.$on("$routeChangeStart", function() {
    $rootScope.onLoad = true;

    if (!$cookies.get("LASTLOGINMESSAGE")) {
      if ($cookies.get("USERTOKEN"))
        $http.get($rootScope.apiBaseURL + "/accountadministration/logout/" + $cookies.get("USERTOKEN"));
      $rootScope.onUserTokenError();
    }
  });

  // On route change (success)
  $rootScope.$on("$routeChangeSuccess", function(event, current, previous) {
    if (current.$$route)
      $rootScope.title = current.$$route.title;
    $rootScope.onLoad = false;
  });

  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {
    $rootScope.onLoad = false;
  });

}]);