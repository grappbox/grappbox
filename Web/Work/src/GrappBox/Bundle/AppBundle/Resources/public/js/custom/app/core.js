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
  "angularBootstrapMaterial"
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

  $scope.content = { routeList: "", iconList: "", homepage: false };
  $scope.method = { switchProject: "" };

  $scope.content.routeList = {
    "/dashboard": false,
    "/bugtracker": false,
    "/calendar": false,
    "/cloud": false,
    "/profile": false,
    "/settings": false,
    "/timeline": false,
    "/whiteboard": false,
    "/tasks": false,
    "/logout": false
  };

  $scope.content.iconList = {
    "/dashboard": "dashboard",
    "/bugtracker": "bug_report",
    "/calendar": "event",
    "/cloud": "cloud_upload",
    "/profile": "person",
    "/settings": "settings",
    "/timeline": "forum",
    "/whiteboard": "create",
    "/tasks": "view_list",
    "/logout": "exit_to_app"
  };

  // Routine definition
  // Project change button handler
  $scope.method.switchProject = function() {
    localStorageService.clearAll();
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    $rootScope.page.menuFull = false;    

    $location.path("/");
  };

  // Routine definition
  // Check if the request route is not a subsection of another one
  var isSubRouteOf = function(routeToTest, routeToLoad) {
    var isRouteKnown = false;

    if (routeToLoad.indexOf(routeToTest) > -1) {
      $scope.content.routeList["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // On route change (start)
  $scope.$on("$routeChangeStart", function() {
    var routeToLoad = $location.path();

    angular.forEach($scope.content.routeList, function(key, value) {
      if (value === routeToLoad)
        $scope.content.routeList[value] = true;
      else if (isSubRouteOf(value, routeToLoad))
        $scope.content.routeList[value] = true;
      else
        $scope.content.routeList[value] = false;
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



/* =============================================== */
/* ==================== OTHER ==================== */
/* =============================================== */

(function($) {

  // Auto page height (depending on page content)
  $(window).bind("load resize", function() {
    newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height);
    $("#app-wrapper").css("min-height", (newHeight < 1 ? 1 : newHeight) + "px");
  });
  
})(jQuery);