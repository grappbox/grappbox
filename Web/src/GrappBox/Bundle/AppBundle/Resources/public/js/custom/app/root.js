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
app.run(["$rootScope", "$base64", "localStorageService", "$location", "$cookies", "$window", "$http", function($rootScope, $base64, localStorageService, $location, $cookies, $window, $http) {

  /* ==================== INITIALIZATION ==================== */

  // ROOTSCOPE variables
  $rootScope.api = { version: "", url: "" };
  $rootScope.menu = { open: true, toggle: "" };
  $rootScope.page = { onLoad: false, title: "", isHome: false };
  $rootScope.project = { set: false, id: "", name: "", switch : "" };
  $rootScope.user = { id : "", token: "", firstname: "", lastname: "", email: "" };

  $rootScope.api.version = "V0.2"
  $rootScope.api.url = "http://api.grappbox.com/" + $rootScope.api.version;

  $rootScope.user.id = $base64.decode($cookies.get("ID"));
  $rootScope.user.token = $base64.decode($cookies.get("TOKEN"));



  /* ==================== ROOTSCOPE ROUTINES ==================== */

  // ROOTSCOPE routine definition
  // Toggle menu state
  $rootScope.menu.toggle = function() {
    $rootScope.menu.open = !$rootScope.menu.open;
	};

  // Routine definition
  // Project change button handler
  $rootScope.project.switch = function() {
    $rootScope.project.id = null;
    $rootScope.project.name = null;
    $rootScope.project.set = false;
    localStorageService.clearAll();
    $location.path("/");
  };

  // ROOTSCOPE routine definition
  // Clear cookies and redirect user to login (with error)
  $rootScope.onUserTokenError = function() {
    $cookies.put("LOGIN", $base64.encode("_denied"), { path: "/" });
    $cookies.remove("TOKEN", { path: "/" });
    $cookies.remove("ID", { path: "/" });
    $window.location.href = "/login";
  };



  /* ==================== ROOTSCOPE PAGE CHANGE HANDLERS ==================== */

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
      $rootScope.page.isHome = current.$$route.homepage;
    }
    $http.get($rootScope.api.url + "/user/basicinformations/" + $rootScope.user.token).then(
      function onGetBasicInformationsSuccess(response) {
        var data = (response.data && Object.keys(response.data.data).length ? response.data.data : null);

        if (!data)
          $rootScope.onUserTokenError();

        $rootScope.user.firstname = data.firstname;
        $rootScope.user.lastname = data.lastname;
        $rootScope.user.email = data.email;
      },
      function onGetBasicInformationsFail(response) {
        $rootScope.onUserTokenError();
      }
    );

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