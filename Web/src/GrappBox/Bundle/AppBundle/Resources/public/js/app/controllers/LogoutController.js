/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP logout
app.controller("LogoutController", ["$cookies", "$http", "localStorageService", "notificationFactory", "$rootScope", "$window",
    function($cookies, $http, localStorageService, notificationFactory, $rootScope, $window) {

  /* ==================== LOGOUT ==================== */

  $http.get($rootScope.api.url + "/account/logout", { headers: { "Authorization": $rootScope.user.token }}).then(
    function logoutSuccess() {
      $cookies.remove("G_LOGIN", { path: "/" });
      $cookies.remove("G_TOKEN", { path: "/" });
      $cookies.remove("G_ID", { path: "/" });
      $cookies.remove("G_CUSTOMER", { path: "/" });
      localStorageService.clearAll();
      notificationFactory.clear();
      $window.location.href = "/";
    },
    function logoutFail() {
      $rootScope.reject();
    }
  );

}]);