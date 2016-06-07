/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ================================================== */
/* ==================== GRAPPBOX ==================== */
/* ================================================== */

/**
* GRAPPBOX
* APP definition
*
*/
var app = angular.module("grappbox", [
  "ngRoute",
  "ngCookies",
  "ngAnimate",
  "ngTagsInput",
  "ngAside",
  "panhandler",
  "naif.base64",
  "mwl.calendar",
  "ui.bootstrap",
  "ui-notification",
  "ngHamburger",
  "LocalStorageModule",
  "angularBootstrapMaterial"
]);



/* ==================== CONFIGURATION ==================== */

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

// Local storage settings
app.config(function (localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix('grappbox')
    .setStorageCookie(30, '/')
    .setStorageType('localStorage')
    .setNotify(true, true);
});

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



/* ==================== INITIALIZATION ==================== */

// Controller definition
// GrappBox (main)
app.controller("grappboxController", ["$scope", "$aside", "$location", function($scope, $aside, $location) {
  var modalInstance_asideMenu = "";

  $scope.routeList = {
    "/": false,
    "/bugtracker": false,
    "/calendar": false,
    "/cloud": false,
    "/notifications": false,
    "/profile": false,
    "/project": false,
    "/timeline": false,
    "/whiteboard": false
  };
  
  $scope.iconList = {
    "/": "dashboard",
    "/bugtracker": "computer",
    "/calendar": "event",
    "/cloud": "cloud_upload",
    "/notifications": "notifications",
    "/profile": "person",
    "/project": "folder",
    "/timeline": "vertical_align_center",
    "/whiteboard": "create",
    "/logout" : "exit_to_app"
  };

  $scope.app_toggleAsideMenu = function() {
    modalInstance_asideMenu = $aside.open({
      placement: "left",
      size: "sm",
      backdrop: "true",
      templateUrl: "app_asideMenu.html",
      controller: "app_asideMenu",
      resolve: {
         routeList: function () {
           return $scope.routeList;
         },
         iconList: function () {
           return $scope.iconList;
         }
       }
     });
  };

  // Routine definition
  // Check if the request route is not a subsection of another one
  var isSubRouteOf = function(routeToTest, newRoute) {
    var isRouteKnown = false;

    if (newRoute.indexOf(routeToTest) > -1) { 
      $scope.routeList["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // On route change (start)
  $scope.$on("$routeChangeStart", function() {
    var newRoute = $location.path();
    var isRouteKnown = false;

    angular.forEach($scope.routeList, function(isCurrentPathActive, currentPath) {
      if (currentPath === newRoute) {
        isRouteKnown = true;
        $scope.routeList[currentPath] = true;
      }
      else
        $scope.routeList[currentPath] = false;
    });
    if (!isRouteKnown)
      isRouteKnown = (isSubRouteOf("bugtracker", newRoute) ? true : (isSubRouteOf("cloud", newRoute) ? true : (isSubRouteOf("whiteboard", newRoute) ? true : false)));
  });

}]);



/* ==================== ROOTSCOPE DEFINITION ==================== */

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