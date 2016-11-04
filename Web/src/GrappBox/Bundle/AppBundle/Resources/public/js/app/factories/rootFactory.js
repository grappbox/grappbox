/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP ROOTSCOPE checks
app.factory("rootFactory", ["$base64", "$cookies", "$http", "localStorageService", "$location", "$rootScope",
    function($base64, $cookies, $http, localStorageService, $location, $rootScope) {

  /* ==================== ROUTE CHANGE START ==================== */

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

  // Routine definition
  // On route change start (ROOTSCOPE)
  var _routeChangeStart = function() {
    if (!$cookies.get("LOGIN") || !$cookies.get("ID")) {
      if ($cookies.get("TOKEN"))
        $http.get($rootScope.api.url + "/account/logout", { headers: { "Authorization": $rootScope.user.token }});
      $rootScope.reject();
    }
    $rootScope.page.current = "/";
    $rootScope.page.load = true;
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

  // Routine definition
  // On route change success (ROOTSCOPE)
  var _routeChangeSuccess = function(current) {
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
      function onError(response) {
        $rootScope.reject();
      }
    );

    if ($rootScope.project.set)
      if (!localStorageService.get("HAS_PROJECT") || !localStorageService.get("PROJECT_ID") || !localStorageService.get("PROJECT_NAME")) {
        $rootScope.project.disconnect(true);
        notificationFactory.error();
      }

    if (!$rootScope.project.set)
      if (localStorageService.get("HAS_PROJECT")) {
        if (!localStorageService.get("PROJECT_ID") || !localStorageService.get("PROJECT_NAME")) {
          $rootScope.project.disconnect(true);
          notificationFactory.error();
        }
        else {
          $rootScope.project.id = $base64.decode(localStorageService.get("PROJECT_ID"));
          $rootScope.project.name = $base64.decode(localStorageService.get("PROJECT_NAME"));
          $rootScope.project.set = true;
        }
      }
    
    $rootScope.page.load = false;
  };



  /* ==================== ROUTE CHANGE ERROR ==================== */

  // Routine definition
  // On route change error (ROOTSCOPE)
  var _routeChangeError = function() {
    $rootScope.page.load = false;
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
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