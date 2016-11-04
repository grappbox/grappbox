/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =================================================== */
/* ==================== ROOTSCOPE ==================== */
/* =================================================== */

// ROOTSCOPE definition
app.run(["accessFactory", "$base64", "$cookies", "$http", "localStorageService", "$location", "notificationFactory", "rootFactory", "$rootScope", "$window",
    function(accessFactory, $base64, $cookies, $http, localStorageService, $location, notificationFactory, rootFactory, $rootScope, $window) {

  /* ==================== INITIALIZATION ==================== */

  // ROOTSCOPE variables
  $rootScope.api = { version: "", url: "" };
  $rootScope.user = { id : "", token: "", firstname: "", lastname: "", email: "" };
  $rootScope.page = { load: false, title: "" };
  $rootScope.project = { set: false, id: "", name: "", disconnect : "" };
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
  $rootScope.project.disconnect = function(error) {
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    localStorageService.clearAll();
    if (!error)
      notificationFactory.clear();
    $location.path("/");
    $rootScope.path.current = "/";
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



  /* ==================== ON ROUTE EVENTS ==================== */

  // ROOTSCOPE routine
  // On route change (start)
  $rootScope.$on("$routeChangeStart", function() {
    rootFactory.routeChangeStart();
  });

  // ROOTSCOPE routine
  // On route change (success)
  $rootScope.$on("$routeChangeSuccess", function(event, current, previous) {
    rootFactory.routeChangeSuccess(current);
  });

  // ROOTSCOPE routine
  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {    
    rootFactory.routeChangeError();
  });

}]);