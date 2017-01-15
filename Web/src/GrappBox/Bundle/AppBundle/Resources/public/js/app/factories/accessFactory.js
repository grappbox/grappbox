/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP page access checks and actions
// (login, logout, bugtracker, project, whiteboard)
app.factory("accessFactory", ["$base64", "$http", "localStorageService", "$location", "notificationFactory", "$q", "$rootScope", "$route",
    function($base64, $http, localStorageService, $location, notificationFactory, $q, $rootScope, $route) {

  /* ==================== LOGIN ==================== */

  // Routine definition
  // APP login
  var _login = function() {
    var deferred = $q.defer();
    $location.path("/");
    $rootScope.path.current = "/";
    deferred.resolve(true);
    return deferred.promise;
  };



  /* ==================== BUGTRACKER ==================== */

  // Routine definition
  // APP bugtracker-related pages access
  var _bugAvailable = function() {
    var deferred = $q.defer();

    if ($route.current.params.id == 0) {
      deferred.resolve();
      return deferred.promise;
    }

    $http.get($rootScope.api.url + "/bugtracker/ticket/" + $route.current.params.id, { headers: { "Authorization": $rootScope.user.token }})
    .then(function getTicketSuccess(response) {
      deferred.resolve();
    },
    function getTicketFail(response) {
      if (response && response.data && response.data.info && response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "4.1.3":
          deferred.reject();
          $rootScope.reject();
          break;

          case "4.1.4":
          deferred.reject();
          $location.path("bugtracker");
          notificationFactory.warning("Ticket not found.");
          break;

          case "4.1.9":
          deferred.reject();
          $location.path("bugtracker");
          notificationFactory.warning("You don't have permission to see this ticket.");
          break;

          default:
          deferred.reject();
          $location.path("bugtracker");
          notificationFactory.error();
          break;
        }
      }
      else
        $rootScope.reject(true);
    });

    return deferred.promise;
  };



  /* ==================== PROJECT (AVAILABILITY) ==================== */

  // Routine definition
  // APP project-related pages access
  var _projectAvailable = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/project/" + $route.current.params.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function projectAvailable() {
        deferred.resolve();
      },
      function projectNotAvailable(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "6.3.3":
            deferred.reject();
            $rootScope.reject();
            break;

            case "6.3.4":
            deferred.reject();
            $rootScope.project.logout(true);
            notificationFactory.warning("Project not found.");
            break;

            case "6.3.9":
            deferred.reject();
            $rootScope.project.logout(true);
            notificationFactory.warning("You don't have access to this project.");
            break;

            default:
            deferred.reject();
            $rootScope.project.logout(true);
            notificationFactory.error();
            break;
          }
        }
        else
          $rootScope.reject(true);
      }
    );

    return deferred.promise;
  };



  /* ==================== PROJECT (SELECTION) ==================== */

  // Routine definition
  // APP project selection check
  var _projectSelected = function() {
    var deferred = $q.defer();

    if (!$rootScope.project.set)
      deferred.resolve();
    else
      if (!localStorageService.get("project.id"))
        deferred.resolve();
      else {
        $location.path("/dashboard/" + $base64.decode(localStorageService.get("project.id")));
        deferred.resolve();
      }

    return deferred.promise;
  };



  /* ==================== PROJECT SETTINGS ==================== */

  // Routine definition
  // APP project settings pages access
  var _projectSettingsAvailable = function() {
    var deferred = $q.defer();

    if ($route.current.params.project_id == 0)
      deferred.resolve();
    else
      $http.get($rootScope.api.url + "/project/" + $route.current.params.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
        function projectSettingsAvailable(response) {
          deferred.resolve();
        },
        function projectSettingsNotAvailable(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "6.3.3":
              deferred.reject();
              $rootScope.reject();
              break;

              case "6.3.4":
              deferred.reject();
              $location.path("/");
              notificationFactory.warning("Project not found.");
              break;

              case "6.3.9":
              deferred.reject();
              $location.path("/");
              notificationFactory.warning("You don't have access to the settings of this project.");
              break;

              default:
              deferred.reject();
              $location.path("/");
              notificationFactory.error();
              break;
            }
          }
          else
            $rootScope.reject(true);
        }
      );

    return deferred.promise;
  };



  /* ==================== TASKS ==================== */

  // Routine definition
  // APP task-related pages access
  var _taskAvailable = function() {
    var deferred = $q.defer();

    if ($route.current.params.id == 0)
      deferred.resolve();
    else
      $http.get($rootScope.api.url + "/task/" + $route.current.params.id, { headers: { "Authorization": $rootScope.user.token }}).then(
        function taskAvailable(response) {
          deferred.resolve();
        },
        function taskNotAvailable(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "12.3.3":
              deferred.reject();
              $rootScope.reject();
              break;

              case "12.3.4":
              deferred.reject();
              $location.path("tasks");
              notificationFactory.warning("Task not found.");
              break;

              case "12.3.9":
              deferred.reject();
              $location.path("tasks");
              notificationFactory.warning("You don't have access to this task.");
              break;

              default:
              deferred.reject();
              $location.path("tasks");
              notificationFactory.error();
              break;
            }
          }
          else
            $rootScope.reject(true);
        }
      );

    return deferred.promise;
  };



  /* ==================== WHITEBOARD ==================== */

  // Routine definition
  // APP whiteboard access
  var _whiteboardAvailable = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/whiteboards/" + $route.current.params.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function whiteboardListSuccess(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
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
        else
          $rootScope.reject(true);
      },
      function whiteboardListFail(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
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
        else
          $rootScope.reject(true);
      }
    );

    return deferred.promise;
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    login: function() {
      return _login();
    },

    bugAvailable: function() {
      return _bugAvailable();
    },

    projectAvailable: function() {
      return _projectAvailable();
    },

    projectSelected: function() {
      return _projectSelected();
    },

    projectSettingsAvailable: function() {
      return _projectSettingsAvailable();
    },

    taskAvailable: function() {
      return _taskAvailable();
    },

    whiteboardAvailable: function() {
      return _whiteboardAvailable();
    }
  };

}]);