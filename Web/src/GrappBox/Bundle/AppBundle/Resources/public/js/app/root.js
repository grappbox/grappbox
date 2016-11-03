/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =================================================== */
/* ==================== ROOTSCOPE ==================== */
/* =================================================== */

// ROOTSCOPE definition
app.run(["$base64", "$cookies", "$http", "localStorageService", "$location", "notificationFactory", "$rootScope", "$window",
    function($base64, $cookies, $http, localStorageService, $location, notificationFactory, $rootScope, $window) {

  /* ==================== INITIALIZATION ==================== */

  // ROOTSCOPE variables
  $rootScope.api = { version: "", url: "" };
  $rootScope.user = { id : "", token: "", firstname: "", lastname: "", email: "" };
  $rootScope.page = { load: false, title: "" };
  $rootScope.project = { set: false, id: "", name: "", change : "" };
  $rootScope.sidebar = { open: true, toggle: "" };

  $rootScope.api.version = "0.3";
  $rootScope.api.url = "https://api.grappbox.com/" + $rootScope.api.version;

  $rootScope.user.id = $base64.decode($cookies.get("ID"));
  $rootScope.user.token = $base64.decode($cookies.get("TOKEN"));

  $rootScope.path = {
    current: "/",
    colors: {
      "/": "default", "/bugtracker": "purple", "/calendar": "blue", "/cloud": "yellow", "/dashboard": "red", "/gantt":"blue", "/logout": "default",       
      "/notifications": "default", "/profile": "red", "/settings": "red", "/tasks": "blue", "/timeline": "orange", "/whiteboard": "green"
    },
    icons: {
      "/": "default", "/bugtracker": "bug_report", "/calendar": "event", "/cloud": "cloud_upload", "/dashboard": "dashboard", "/gantt": "sort", "/logout": "exit_to_app",
      "/notifications": "notifications", "/profile": "person", "/settings": "settings", "/tasks": "view_list", "/timeline": "forum", "/whiteboard": "create"
    },
    routes: {
      "/bugtracker": false, "/calendar": false, "/cloud": false, "/dashboard": false, "/gantt": false, "/logout": false,
      "/notifications": false, "/profile": false, "/settings": false, "/tasks": false, "/timeline": false, "/whiteboard": false
    }
  };



  /* ==================== SIDEBAR ==================== */

  // ROOTSCOPE routine
  // Toggle sidebar state
  $rootScope.sidebar.toggle = function() {
    $rootScope.sidebar.open = !$rootScope.sidebar.open;
  };



  /* ==================== PROJECT SELECTION ==================== */

  // Project change button handler
  $rootScope.project.change = function() {
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    localStorageService.clearAll();
    notificationFactory.clear();
    $location.path("/");
  };



  /* ==================== USER TOKEN ERROR ==================== */

  // ROOTSCOPE routine
  // Clear cookies and redirect user to login (with error)
  $rootScope.reject = function() {
    $cookies.put("LOGIN", $base64.encode("_denied"), { path: "/" });
    $cookies.remove("TOKEN", { path: "/" });
    $cookies.remove("ID", { path: "/" });
    $window.location.href = "/login";
  };



  /* ==================== ON ROUTE CHANGE START ==================== */

  // Routine definition (local)
  // Check if the request route is not a subsection of another one
  var _isRouteFrom = function(routeToTest, routeToLoad) {
    var isRouteKnown = false;

    if (routeToLoad.indexOf(routeToTest) > -1) {
      $rootScope.path.routes["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // ROOTSCOPE routine
  // On route change (start)
  $rootScope.$on("$routeChangeStart", function() {
    if (!$cookies.get("LOGIN") || !$cookies.get("ID")) {
      if ($cookies.get("TOKEN"))
        $http.get($rootScope.api.url + "/account/logout", { headers: { "Authorization": $rootScope.user.token }});
      $rootScope.reject();
    }

    notificationFactory.clear();
    $rootScope.page.load = true;
    $rootScope.path.current = "/";
    angular.forEach($rootScope.path.routes, function(key, value) {
      if (value === $location.path() || _isRouteFrom(value, $location.path())) {
        $rootScope.path.routes[value] = true;
        $rootScope.path.current = value;
      }
      else
        $rootScope.path.routes[value] = false;
    }, $rootScope);
  });



  /* ==================== ON ROUTE CHANGE SUCCESS ==================== */

  // APP routine
  // On route change (success)
  $rootScope.$on("$routeChangeSuccess", function(event, current, previous) {
    if (current.$$route)
      $rootScope.page.title = current.$$route.title;
    $http.get($rootScope.api.url + "/user", { headers: { "Authorization": $rootScope.user.token }}).then(
      function onSuccess(response) {
        if (angular.isUndefined(response.data.data))
          $rootScope.reject();
        $rootScope.user.firstname = response.data.data.firstname;
        $rootScope.user.lastname = response.data.data.lastname;
        $rootScope.user.email = response.data.data.email;
      },
      function onError(response) { $rootScope.reject(); }
    );

    if (!$rootScope.project.set)
      if (localStorageService.get("HAS_PROJECT")) {
        $rootScope.project.id = $base64.decode(localStorageService.get("PROJECT_ID"));
        $rootScope.project.name = $base64.decode(localStorageService.get("PROJECT_NAME"));
        $rootScope.project.set = true;
      }
    $rootScope.page.load = false;
  });



  /* ==================== ON ROUTE CHANGE ERROR ==================== */

  // APP routine
  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {
    $rootScope.page.load = false;
  });

}]);