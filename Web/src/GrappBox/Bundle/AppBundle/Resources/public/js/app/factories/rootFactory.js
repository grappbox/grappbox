/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP ROOTSCOPE checks
app.factory("rootFactory", ["$base64", "$cookies", "$http", "localStorageService", "$location", "notificationFactory", "$rootScope", "$window",
    function($base64, $cookies, $http, localStorageService, $location, notificationFactory, $rootScope, $window) {

  /* ==================== PROJECT LOGOUT ==================== */
 
  // ROOTSCOPE routine definition 
  // Project logout handler
  var _logout = function(error) {
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    localStorageService.clearAll();
    notificationFactory.clear();
    if (error)
      notificationFactory.error();
    $location.path("/");
    $rootScope.path.current = "/";
  };



  /* ==================== REJECT ==================== */

  // ROOTSCOPE routine definition
  // Clear cookies and redirect to login (with error)
  var _reject = function() {
    $cookies.put("G_LOGIN", $base64.encode("_denied"), { path: "/" });
    $cookies.remove("G_TOKEN", { path: "/" });
    $cookies.remove("G_ID", { path: "/" });
    $window.location.href = "/login";
  };



  /* ==================== ROUTE CHANGE START ==================== */

  // ROOTSCOPE routine definition (local)
  // Check if the request route is not a subsection of another one
  var _isRouteFrom = function(routeToTest, routeToLoad) {
    var isRouteKnown = false;

    if (routeToLoad.indexOf(routeToTest) > -1) {
      $rootScope.path.routes["/" + routeToTest] = true;
      isRouteKnown = true;
    }
    return isRouteKnown;
  };

  // ROOTSCOPE routine definition
  // On route change start
  var _routeChangeStart = function() {
    $rootScope.page.load = true;

    // Authentication cookies check
    if (!$cookies.get("G_LOGIN") || !$cookies.get("G_ID")) {
      if ($cookies.get("G_TOKEN"))
        $http.get($rootScope.api.url + "/account/logout", { headers: { "Authorization": $rootScope.user.token }});
      $rootScope.reject();
    }

    // Authentication token check
    $http.get($rootScope.api.url + "/user", { headers: { "Authorization": $rootScope.user.token }}).then(
      function onSuccess(response) {
        if (!response || !response.data || !response.data.data)
          $rootScope.reject();
        $rootScope.user.firstname = response.data.data.firstname;
        $rootScope.user.lastname = response.data.data.lastname;
        $rootScope.user.email = response.data.data.email;
      },
      function onError(response) {
        $rootScope.reject();
      }
    );

    // Project selection check
    if ($rootScope.project.set) {
      if (!localStorageService.get("project.set") || !localStorageService.get("project.id") || !localStorageService.get("project.name"))
        $rootScope.project.logout(true);
    }
    else {
      if (localStorageService.get("project.set") && localStorageService.get("project.id") && localStorageService.get("project.name")) {
        $rootScope.project.id = $base64.decode(localStorageService.get("PROJECT_ID"));
        $rootScope.project.name = $base64.decode(localStorageService.get("PROJECT_NAME"));
        $rootScope.project.set = true;
      }
    } 

    // Route settings
    angular.forEach($rootScope.path.routes, function(key, value) {
      if (value === $location.path() || _isRouteFrom(value, $location.path())) {
        $rootScope.path.routes[value] = true;
        $rootScope.path.current = value;
      }
      else
        $rootScope.path.routes[value] = false;
    }, $rootScope);
  };



  /* ==================== ROUTE CHANGE SUCCESS ==================== */

  // ROOTSCOPE routine definition
  // On route change success
  var _routeChangeSuccess = function(current) {
    $rootScope.page.load = false;
    if (current.$$route)
      $rootScope.page.title = current.$$route.title;
  };



  /* ==================== ROUTE CHANGE ERROR ==================== */

  // ROOTSCOPE routine definition
  // On route change error
  var _routeChangeError = function() {
    $rootScope.page.load = false;
    notificationFactory.error();
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    logout: function(error) {
      return _logout(error);
    },

    reject: function() {
      return _reject();
    },

    routeChangeStart: function() {
      return _routeChangeStart();
    },

    routeChangeSuccess: function(current) {
      return _routeChangeSuccess(current);
    },

    routeChangeError: function() {
      return _routeChangeError();
    }
  };

}]);