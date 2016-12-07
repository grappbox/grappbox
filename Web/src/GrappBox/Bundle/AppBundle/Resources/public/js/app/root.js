/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =================================================== */
/* ==================== ROOTSCOPE ==================== */
/* =================================================== */

// ROOTSCOPE definition
app.run(["$base64", "$cookies", "rootFactory", "$rootScope",
    function($base64, $cookies, rootFactory, $rootScope) {

  /* ==================== INITIALIZATION ==================== */

  // ROOTSCOPE variables
  $rootScope.api = { version: "", url: "" };
  $rootScope.user = { id : "", token: "", firstname: "", lastname: "", email: "" };
  $rootScope.page = { load: false, title: "" };
  $rootScope.project = { set: false, id: "", name: "", logout: "" };
  $rootScope.sidebar = { open: true, toggle: "" };

  $rootScope.api.version = "0.3";
  $rootScope.api.url = "https://api.grappbox.com/" + $rootScope.api.version;

  $rootScope.user.id = $base64.decode($cookies.get("G_ID"));
  $rootScope.user.token = $base64.decode($cookies.get("G_TOKEN"));

  $rootScope.path = {
    current: "/",
    colors: {
      "/": "default",
      "/bugtracker": "purple",
      "/calendar": "blue",
      "/cloud": "yellow",
      "/dashboard": "red",
      "/gantt":"blue",
      "/logout": "default",      
      "/notifications": "default",
      "/profile": "gray",
      "/settings": "red",
      "/statistics": "red",
      "/talk": "orange",
      "/tasks": "blue",
      "/whiteboard": "green"
    },
    icons: {
      "/": "default",
      "/bugtracker": "bug_report",
      "/calendar": "event_note",
      "/cloud": "cloud",
      "/dashboard": "view_quilt",
      "/gantt": "sort",
      "/logout": "exit_to_app",
      "/notifications": "notifications",
      "/profile": "person",
      "/statistics": "timeline",
      "/settings": "settings",
      "/talk": "chat",
      "/tasks": "assignment_turned_in",
      "/whiteboard": "create"
    },
    routes: {
      "/bugtracker": false,
      "/calendar": false,
      "/cloud": false,
      "/dashboard": false,
      "/gantt": false,
      "/logout": false,
      "/notifications": false,
      "/profile": false,
      "/settings": false,
      "/statistics": false,
      "/talk": false,
      "/tasks": false,
      "/whiteboard": false
    }
  };



  /* ==================== SIDEBAR ==================== */

  // ROOTSCOPE routine
  // Toggle sidebar state
  $rootScope.sidebar.toggle = function(state) {
    if (angular.isUndefined(state))
      $rootScope.sidebar.open = !$rootScope.sidebar.open;
    else
      $rootScope.sidebar.open = state;
  };



  /* ==================== PROJECT SELECTION ==================== */

  // ROOTSCOPE routine
  // Project logout handler
  $rootScope.project.logout = function(error) {
    rootFactory.logout(error);
  };



  /* ==================== USER TOKEN ERROR ==================== */

  // ROOTSCOPE routine
  // Clear cookies and redirect to login (with error)
  $rootScope.reject = function() {
    rootFactory.reject();
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