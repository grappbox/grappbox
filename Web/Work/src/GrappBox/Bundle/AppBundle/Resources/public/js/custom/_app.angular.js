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
var app = angular.module("grappbox", ["ngRoute", "ngCookies", "ngAnimate", "ui.bootstrap", "panhandler", "ui-notification", "naif.base64", "ngTagsInput"]);

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

// Main controller definition
app.controller("grappboxController", ["$scope", "$location", function($scope, $location) {

  $scope.isPageActive = function(route) {
    var isPageActive = false;
    switch (route) {
      case "/cloud":
      isPageActive = ($location.path().indexOf("cloud") > -1);
      break;
      case "/bugtracker":
      isPageActive = ($location.path().indexOf("bugtracker") > -1);
      break;
      case "/whiteboard":
      isPageActive = ($location.path().indexOf("whiteboard") > -1);
      break;
      default:
      isPageActive = (route === $location.path());
      break;
    };
    return isPageActive;
  }
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
    $rootScope.onLoad = false;

    if (current.$$route)
      $rootScope.title = current.$$route.title;
  });

  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {
    $rootScope.onLoad = false;
  });

}]);