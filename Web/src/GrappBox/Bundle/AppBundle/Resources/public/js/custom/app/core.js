/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ==================================================== */
/* ==================== DEFINITION ==================== */
/* ==================================================== */

// GRAPPBOX APP DEFINITION
var app = angular.module("grappbox", [
  "ngRoute",
  "ngCookies",
  "ngAnimate",
  "ngTagsInput",
  "panhandler",
  "base64",
  "naif.base64",
  "mwl.calendar",
  "ui.bootstrap",
  "ui-notification",
  "LocalStorageModule",
  "angularBootstrapMaterial",
  "gantt",
  "gantt.tooltips",
  "ui.tree",
  "gantt.tree",
  "gantt.groups",
  "gantt.progress"
]);



/* ======================================================= */
/* ==================== CONFIGURATION ==================== */
/* ======================================================= */

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
app.config(function(localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix("GRAPPBOX_")
    .setStorageCookie(30, "/")
    .setStorageType("localStorage")
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



/* ======================================================== */
/* ==================== INITIALIZATION ==================== */
/* ======================================================== */

// Controller definition
// GrappBox (main)
app.controller("grappboxController", ["$rootScope", "$scope", "localStorageService", "$location", function($rootScope, $scope, localStorageService, $location) {

  $scope.app = { routeList: "", iconList: "" };

  $scope.app.routeList = {
    "/notifications": false,
    "/dashboard": false,
    "/bugtracker": false,
    "/calendar": false,
    "/cloud": false,
    "/profile": false,
    "/settings": false,
    "/timeline": false,
    "/whiteboard": false,
    "/tasks": false,
    "/gantt":false,
    "/logout": false
  };

  $scope.app.iconList = {
    "/notifications": "notifications",
    "/dashboard": "dashboard",
    "/bugtracker": "bug_report",
    "/calendar": "event",
    "/cloud": "cloud_upload",
    "/profile": "person",
    "/settings": "settings",
    "/timeline": "forum",
    "/whiteboard": "create",
    "/tasks": "view_list",
    "/gantt": "sort",
    "/logout": "exit_to_app"
  };

  // Routine definition
  // Check if the request route is not a subsection of another one
  var _isRouteFrom = function(routeToTest, routeToLoad) {
    var isRouteKnown = false;

    if (routeToLoad.indexOf(routeToTest) > -1) {
      $scope.app.routeList["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // On route change (start)
  $scope.$on("$routeChangeStart", function() {
    var routeToLoad = $location.path();

    angular.forEach($scope.app.routeList, function(key, value) {
      if (value === routeToLoad)
        $scope.app.routeList[value] = true;
      else if (_isRouteFrom(value, routeToLoad))
        $scope.app.routeList[value] = true;
      else
        $scope.app.routeList[value] = false;
    });
  });

}]);



/* ================================================= */
/* ==================== SIDEBAR ==================== */
/* ================================================= */

// Directive definition
// Sidebar
app.directive("sidebarDirective", function() {
  return {
    link: function(scope, element, attr) {
      scope.$watch(attr.sidebarDirective, function(value) {
        if (value) {
          element.addClass("open");
          return ;
        }
        element.removeClass("open");
      });
    }
  };
});
