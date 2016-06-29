/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =================================================== */
/* ==================== ROOTSCOPE ==================== */
/* =================================================== */

/**
* ROOTSCOPE definition
* "layout, loading, api, project, user"
*
*/
app.run(["$rootScope", "$location", "$cookies", "$http", "$window", "localStorageService", "$base64", function($rootScope, $location, $cookies, $http, $window, localStorageService, $base64) {

  // ROOTSCOPE variables
  $rootScope.api = {}
  $rootScope.user = {};
  $rootScope.project = {};
  $rootScope.page = {};

  $rootScope.api.version = "V0.2"
  $rootScope.api.url = "http://api.grappbox.com/app_dev.php/" + $rootScope.api.version;

  $rootScope.page.menuState = true;
  $rootScope.page.onLoad = false;
  $rootScope.project.set = false;

  $rootScope.user.id = $base64.decode($cookies.get("ID"));
  $rootScope.user.token = $base64.decode($cookies.get("TOKEN"));


  // ROOTSCOPE routine definition
  // Toggle menu state
  $rootScope.page.toggleMenuState = function() {
    $rootScope.page.menuState = !$rootScope.page.menuState;
	};


  // ROOTSCOPE routine definition
  // Clear cookies and redirect user to login (with error)
  $rootScope.onUserTokenError = function() {
    $cookies.put("LOGIN", $base64.encode("_denied"), { path: "/" });
    $cookies.remove("TOKEN", { path: "/" });
    $cookies.remove("ID", { path: "/" });
    $window.location.href = "/login";
  };


  // ROOTSCOPE handler definition
  // On route change (start)
  $rootScope.$on("$routeChangeStart", function() {
    $rootScope.page.onLoad = true;

    if (!$cookies.get("LOGIN") || !$cookies.get("ID")) {
      if ($cookies.get("TOKEN"))
        $http.get($rootScope.api.url + "/accountadministration/logout/" + $rootScope.user.token);
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

    $http.get($rootScope.api.url + "/user/basicinformations/" + $rootScope.user.token)
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
    }
    $rootScope.page.onLoad = false;
  });


  // ROOTSCOPE handler definition
  // On route change (error)
  $rootScope.$on("$routeChangeError", function() {
    $rootScope.page.onLoad = false;
  });

}]);