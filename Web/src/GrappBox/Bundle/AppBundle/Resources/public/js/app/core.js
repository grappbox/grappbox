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
  "base64",
  "naif.base64",
  "mwl.calendar",
  "ui.bootstrap",
  "ui-notification",
  "LocalStorageModule",
  "ui.tree",
  "ang-drag-drop",
  'gantt',
  //'gantt.sortable',
  'gantt.movable',
  //'gantt.drawtask',
  'gantt.tooltips',
  //'gantt.bounds',
  'gantt.progress',
  'gantt.table',
  'gantt.tree',
  'gantt.groups',
  'gantt.dependencies',
  //'gantt.overlap',
  'gantt.resizeSensor'//,
  //'mgcrea.ngStrap'
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
app.config(["localStorageServiceProvider", function(localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix("GRAPPBOX_")
    .setStorageCookie(30, "/")
    .setStorageType("localStorage")
    .setNotify(true, true);
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



/* ======================================================== */
/* ==================== INITIALIZATION ==================== */
/* ======================================================== */

// Controller definition
// GrappBox (main)
app.controller("grappboxController", ["$rootScope", "$scope", "localStorageService", "$location",
    function($rootScope, $scope, localStorageService, $location) {

  $scope.app = {
    current: "/",
    colors: {
      "/": "default",
      "/notifications": "default",
      "/dashboard": "red",
      "/bugtracker": "purple",
      "/calendar": "blue",
      "/cloud": "yellow",
      "/profile": "red",
      "/settings": "red",
      "/timeline": "orange",
      "/whiteboard": "green",
      "/tasks": "blue",
      "/gantt":"blue",
      "/logout": "default"
    },
    icons: {
      "/": "default",
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
    },
    routes: {
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
    }
  };


  // Routine definition (local)
  // Check if the request route is not a subsection of another one
  var _isRouteFrom = function(routeToTest, routeToLoad) {
    var isRouteKnown = false;

    if (routeToLoad.indexOf(routeToTest) > -1) {
      $scope.app.routes["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };


  // On route change (start)
  $scope.$on("$routeChangeStart", function() {
    var routeToLoad = $location.path();

    $scope.app.current = "/";
    angular.forEach($scope.app.routes, function(key, value) {
      if (value === routeToLoad || _isRouteFrom(value, routeToLoad)) {
        this.app.routes[value] = true;
        this.app.current = value;
      }
      else {
        this.app.routes[value] = false;
      }
    }, $scope);
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



/* ======================================================= */
/* ==================== MATERIAL DESIGN ================== */
/* ======================================================= */

$(document).ready(function() {
  $.material.init();
});
