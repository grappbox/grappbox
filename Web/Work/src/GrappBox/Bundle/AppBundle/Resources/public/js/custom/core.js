/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/



/* ==================================================== */
/* ==================== DEFINITION ==================== */
/* ==================================================== */

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
    .setPrefix('__GRAPPBOX_')
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
    "/bugtracker": "computer",
    "/calendar": "event",
    "/cloud": "cloud_upload",
    "/profile": "person",
    "/settings": "settings",
    "/timeline": "vertical_align_center",
    "/whiteboard": "create",
    "/tasks": "view_list",
    "/logout": "exit_to_app"
  };

  $scope.method.switchProject = function() {
    localStorageService.clearAll();
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    $rootScope.menu.full = false;    

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
app.directive('sidebarDirective', function() {
  return {
    link: function(scope, element, attr) {
      scope.$watch(attr.sidebarDirective, function(value) {
        if (value) {
          element.addClass('open'); 
          return ;
        }
        element.removeClass('open');
      });
    }
  };
});  



/* =================================================== */
/* ==================== ROOTSCOPE ==================== */
/* =================================================== */

/**
* ROOTSCOPE definition
* "layout, loading, apiVersion, apiBaseURL"
*
*/
app.run(["$rootScope", "$location", "$cookies", "$http", "$window", "localStorageService", "$base64", function($rootScope, $location, $cookies, $http, $window, localStorageService, $base64) {

  // ROOTSCOPE variables
  $rootScope.menu = { };
  $rootScope.page = { };
  $rootScope.project = { };
  $rootScope.user = { };

  $rootScope.apiVersion = "V0.2"
  $rootScope.apiBaseURL = "http://api.grappbox.com/app_dev.php/" + $rootScope.apiVersion;

  $rootScope.menu.state = true;
  $rootScope.menu.full = false;
  $rootScope.page.onLoad = false;
  $rootScope.project.set = false;

  // ROOTSCOPE routine definition
  // Toggle menu state
  $rootScope.menu.toggleState = function() {
    $rootScope.menu.state = !$rootScope.menu.state;
};

  // ROOTSCOPE routine definition
  // Clear cookies and redirect user to login (with error)
  $rootScope.onUserTokenError = function() {
    $cookies.put("LASTLOGINMESSAGE", sha512("_DENIED"), { path: "/" });
    $cookies.remove("USERTOKEN", { path: "/" });
    $cookies.remove("CLOUDSAFE", { path: "/" });
    $window.location.href = "/#login";
  };

  // ROOTSCOPE handler definition
  // On route change (start)
  $rootScope.$on("$routeChangeStart", function() {
    $rootScope.page.onLoad = true;

    if (!$cookies.get("LASTLOGINMESSAGE")) {
      if ($cookies.get("USERTOKEN"))
        $http.get($rootScope.apiBaseURL + "/accountadministration/logout/" + $cookies.get("USERTOKEN"));
      $rootScope.onUserTokenError();
    }
  });

  // ROOTSCOPE handler definition
  // On route change (success)
  $rootScope.$on("$routeChangeSuccess", function(event, current, previous) {
    if (current.$$route) {
      $rootScope.page.title = current.$$route.title;
      $rootScope.page.home = current.$$route.homepage;
    }

    $http.get($rootScope.apiBaseURL + "/user/basicinformations/" + $cookies.get("USERTOKEN"))
      .then(function onGetSuccess(response) {
        var data = (response.data && Object.keys(response.data.data).length ? response.data.data : null);

        $rootScope.user.firstname = data.firstname;
        $rootScope.user.lastname = data.lastname;
        $rootScope.user.email = data.email;
      },
      function onGetFail(response) {
        context.rootScope.onUserTokenError();
      });

    if (!$rootScope.project.set)
      if (localStorageService.get("HAS_PROJECT")) {
        $rootScope.project.id = $base64.decode(localStorageService.get("PROJECT_ID"));
        $rootScope.project.name = $base64.decode(localStorageService.get("PROJECT_NAME"));
        $rootScope.project.set = true;
        $rootScope.menu.full = true;
    }

    $rootScope.page.onLoad = false;
  });

  // ROOTSCOPE handler definition
  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {
    $rootScope.page.onLoad = false;
  });

}]);



/* =============================================== */
/* ==================== OTHER ==================== */
/* =============================================== */

/**
* GRAPPBOX
* APP additional scripts definition
*
*/
(function($) {

  // Auto page height (depending on page content)
  $(window).bind("load resize", function() {
    newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height);
    $("#app-wrapper").css("min-height", (newHeight < 1 ? 1 : newHeight) + "px");
  });
  
})(jQuery);