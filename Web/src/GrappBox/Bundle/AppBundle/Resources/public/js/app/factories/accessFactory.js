/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

app.factory("accessFactory", ['notificationFactory', function(notificationFactory) {

  /* ==================== LOGIN ==================== */

  // Routine definition
  // APP login
  var _login = function($location, $q) {
    var deferred = $q.defer();
    $location.path("/");
    deferred.resolve(true);
    return deferred.promise;
  };

  // "_login" routine injection
  _login["$inject"] = ["$location", "$q"];



  /* ==================== LOGOUT ==================== */

  // Routine definition
  // APP logout
  var _logout = function($cookies, $http, $localStorageService, $location, notificationFactory, $q, $rootScope, $window) {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/account/logout", { headers: { 'Authorization': $rootScope.user.token }}).then(
      function logoutSuccess() {
        $cookies.remove("LOGIN", { path: "/" });
        $cookies.remove("TOKEN", { path: "/" });
        $cookies.remove("ID", { path: "/" });
        localStorageService.clearAll();
        notificationFactory.clear();

        $window.location.href = "/";
        deferred.resolve(true);
      },
      function logoutFail() { }
    );

    return deferred.promise;
  };

  // "_logout" routine injection
  _logout["$inject"] = ["$cookies", "$http", "$localStorageService", "$location"," notificationFactory", "$q", "$rootScope", "$window"];



  /* ==================== PROJECT (AVAILABILITY) ==================== */

  // Routine definition
  // APP project-related page access
  var _projectAvailable = function($http, $location, notificationFactory, $q, $rootScope, $route) {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/project/" + $route.current.params.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function projectAvailable() {
        deferred.resolve();
      },
      function projectNotAvailable(response) {
        if (!angular.isUndefined(response.data.info.return_code)) {
          switch(response.data.info.return_code) {
            case "6.3.3":
            deferred.reject();
            $rootScope.reject();
            break;

            case "6.3.4":
            deferred.reject();
            $location.path("./");
            notificationFactory.warning("Project not found.");
            break;

            case "6.3.9":
            deferred.reject();
            $location.path("./");
            notificationFactory.warning("You don\'t have access to this part of the project.");
            break;

            default:
            deferred.reject();
            $location.path("./");
            notificationFactory.error();
            break;
          }
        }
        else {
          deferred.reject();
          $location.path("./");
          notificationFactory.error();
        }
      }
    );

    return deferred.promise;
  };

  // "_projectAvailable" routine injection
  _projectAvailable["$inject"] = ["$http", "$location", "notificationFactory", "$q", "$rootScope", "$route"];



  /* ==================== PROJECT (SELECTION) ==================== */

  // Routine definition
  // APP project selection check
  var _projectSelected = function($base64, localStorageService, $location, notificationFactory, $q, $rootScope) {
    var deferred = $q.defer();
    
    if (localStorageService.get("HAS_PROJECT")) {
      if (!localStorageService.get("PROJECT_ID")) {
        notificationFactory.error();
        $rootScope.project.change();
        deferred.resolve();
      }
      else {
        $location.path("/dashboard/" + $base64.decode(localStorageService.get("PROJECT_ID")));
        deferred.resolve();
      }
    }

    return deferred.promise;
  };

  // "_projectSelected" routine injection
  _projectSelected["$inject"] = ["$base64"," localStorageService", "$location"," notificationFactory", "$q", "$rootScope"];



  /* ==================== WHITEBOARD ==================== */

  // Routine definition
  // APP whiteboard access
  var _whiteboard = function($http, $location, notificationFactory, $q, $rootScope, $route) {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/whiteboards/" + $route.current.params.project_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function whiteboardListSuccess(response) {
        if (!angular.isUndefined(response.data.info.return_code)) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            var whiteboardList = (!angular.isUndefined(response.data.data.array) ? response.data.data.array : null);
            var whiteboardKnown = false;

            angular.forEach(whiteboardList, function(value, key) {
              if (value.id == $route.current.params.id)
                whiteboardKnown = true;
            });

            if (whiteboardKnown)
              deferred.resolve();
            else {
              deferred.reject();
              $location.path("whiteboard/" + $route.current.params.project_id);
              notificationFactory.warning("Whiteboard not found.");
            }
            break;

            case "1.10.3":
            deferred.reject();
            $location.path("whiteboard/" + $route.current.params.project_id);
            notificationFactory.warning("Whiteboard not found.");
            break;

            default:
            deferred.reject();
            $location.path("whiteboard/" + $route.current.params.project_id);
            notificationFactory.error();
            break;
          }
        }
        else {
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          notificationFactory.error();
        }
      },
      function whiteboardListFail(response) {
        if (!angular.isUndefined(response.data.info.return_code)) {
          switch(response.data.info.return_code) {
            case "10.1.3":
            deferred.reject();
            $rootScope.reject();
            break;

            case "10.1.9":
            deferred.reject();
            $location.path("whiteboard/" + $route.current.params.project_id);
            notificationFactory.warning("You don't have sufficient rights to perform this operation.");
            break;

            default:
            deferred.reject();
            $location.path("whiteboard/" + $route.current.params.project_id);
            notificationFactory.error();
            break;
          }
        }
        else {
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          notificationFactory.error();
        }
      }
    );

    return deferred.promise;
  };

  // "_whiteboard" routine injection
  _whiteboard["$inject"] = ["$http", "$location"," notificationFactory", "$q", "$rootScope", "$route"];



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    login: function() {
      return _login();
    },
    
    logout: function() {
      return _logout();
    },
    
    projectAvailable: function() {
      return _projectAvailable();
    },

    projectSelected: function() {
      return _projectSelected();
    },

    whiteboard: function() {
      return _whiteboard();
    }
  };

}]);