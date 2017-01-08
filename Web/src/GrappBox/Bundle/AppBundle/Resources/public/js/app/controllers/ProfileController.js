/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP profile settings
app.controller("ProfileController", ["$http", "notificationFactory", "$rootScope", "$scope",
    function($http, notificationFactory, $rootScope, $scope) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { loaded: false, valid: false };
  $scope.profile = { update: "", updatePassword: "", passwordBlur: "" }
  $scope.user = { firstname: "", lastname: "", birthday: "", avatar: "", email: "", phone: "", country: "", linkedin: "", viadeo: "", twitter: "" };
  $scope.local = { firstname: "", lastname: "", birthday: "", avatar: "", email: "", phone: "", country: "", linkedin: "", viadeo: "", twitter: "" };
  $scope.password = { current: "", new: "", confirmation: "", error: { current: false, new: false } };
  $scope.disabled = { update: false, updatePassword: false }



  /* ==================== GET USER DATA ==================== */

  var userDataReceived = function(response) {
    $scope.view.loaded = true;
    $scope.view.valid = true;
    $scope.user = (response && response.data && response.data.data ? response.data.data : null);

    $scope.local.firstname = $scope.user.firstname;
    $scope.local.lastname = $scope.user.lastname;
    $scope.local.birthday = ($scope.user.birthday != "" ? new Date($scope.user.birthday) : "");
    $scope.local.email = $scope.user.email;
    $scope.local.phone = $scope.user.phone;
    $scope.local.country = $scope.user.country;
    $scope.local.linkedin = $scope.user.linkedin;
    $scope.local.viadeo = $scope.user.viadeo;
    $scope.local.twitter = $scope.user.twitter;
  };

  var userDataNotReceived = function(response) {
    $scope.view.loaded = true;
    $scope.view.valid = false;
    $scope.user = null;
    $scope.local = null;

    if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code == "7.1.3")
      $rootScope.reject();
  };

  // Get current user's data
  $http.get($rootScope.api.url + "/user", { headers: { 'Authorization': $rootScope.user.token }}).then(
    function onSuccess(response) { userDataReceived(response) },
    function onError(response) { userDataNotReceived(response) }
  );



	/* ==================== UPDATE USER PROFILE ==================== */

  var userDataUpdated = function() {
    $scope.disabled.update = false;
    notificationFactory.success("Profile updated. You may have to reload your page to see the changes.");
  };

  var userDataNotUpdated = function(response) {
    $scope.disabled.update = false;
    if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code == "7.1.3")
      $rootScope.reject();
    else {
      $scope.view.loaded = true;
      $scope.view.valid = false;
    }
  };

  // "Update" (profile) button handler
  $scope.profile.update = function() {
    if (!$scope.disabled.update && $scope.local.firstname && $scope.local.lastname) {
      $scope.disabled.update = true;
      $http.put($rootScope.api.url + "/user",
        { data: {
          firstname: $scope.local.firstname,
          lastname: $scope.local.lastname,
          birthday: $scope.local.birthday,
          avatar: ($scope.local.avatar.filename ? $scope.local.avatar.base64 : ''),
          email: $scope.local.email,
          phone: $scope.local.phone,
          country: $scope.local.country,
          linkedin: $scope.local.linkedin,
          viadeo: $scope.local.viadeo,
          twitter: $scope.local.twitter }},
        { headers: { 'Authorization': $rootScope.user.token }}).then(
        function onSuccess() { userDataUpdated() },
        function onError(response) { userDataNotUpdated(response) }
      );
    }
  };



  /* ==================== UPDATE USER PASSWORD ==================== */

  var userPasswordUpdated = function() {
    $scope.disabled.updatePassword = false;
    $scope.password.error.current = false;
    $scope.password.error.new = false;
    $scope.password.current = "";
    $scope.password.new = "";
    $scope.password.confirmation = "";
    notificationFactory.success("Password changed.");
  };

  var userPasswordNotUpdated = function(response) {
    $scope.disabled.updatePassword = false;
    if (response && response.data && response.data.info && response.data.info.return_code) {
      switch (response.data.info.return_code) {
        case "7.1.3":
        $rootScope.reject();
        break;

        case "7.1.4":
        $scope.password.error.current = 2;
        break;

        default:
        $scope.view.valid = false;
        break;        
      }
    }
    else
      $scope.view.valid = false;
  };

  // "Password confirmation" input focusout handler
  $scope.profile.passwordChange = function() {
    $scope.password.error.current = (!$scope.password.current ? 1 : false);
    $scope.password.error.new = (!$scope.password.new ? 1 : ($scope.password.new != $scope.password.confirmation ? 2 : ($scope.password.new.length < 8 ? 3 : false)));
  };

  // "Change" (password) button handler
  $scope.profile.updatePassword = function() {
    if (!$scope.disabled.updatePassword && !$scope.password.error.current && !$scope.password.error.new) {
      $scope.disabled.updatePassword = true;
      $http.put($rootScope.api.url + "/user",
        { data: { oldPassword: $scope.password.current, password: $scope.password.new }},
        { headers: { 'Authorization': $rootScope.user.token }}).then(
        function onSuccess() { userPasswordUpdated() },
        function onError(response) { userPasswordNotUpdated(response) }
      );
    }
  };

}]);