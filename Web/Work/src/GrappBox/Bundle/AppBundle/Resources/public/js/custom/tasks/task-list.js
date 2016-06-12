/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP task page list (one per project)
*
*/
app.controller('taskListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', '$location', function($rootScope, $scope, $routeParams, $http, $cookies, Notification, $location) {

  var content = "";

  // Scope variables initialization
  $scope.data = { onLoad: true, projects: { }, isValid: false };

  // Get all projects where the user is associate with
  var getTasksContent = function() {
    // Get current project task(s)
    $scope.data.projectsTask_onLoad = {};
    $scope.data.projectsTask_content = {};
    $scope.data.projectsTask_message = {};

    // Get all tasks for each project
    context = {"scope": $scope, "rootScope": $rootScope, "cookies": $cookies};
    angular.forEach($scope.data.projects, function(project){
      context.scope.data.projectsTask_onLoad[project.name] = true;

      $http.get(context.rootScope.apiBaseURL + '/tasks/getprojecttasks/' + context.cookies.get('USERTOKEN') + '/' + project.project_id)
        .then(function successCallback(response) {
          context.scope.data.projectsTask_onLoad[project.name] = false;
          context.scope.data.projectsTask_content[project.name] = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
          context.scope.data.projectsTask_message[project.name] = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
        },
        function errorCallback(response) {
          context.scope.data.projectsTask_onLoad[project.name] = false;
          context.scope.data.projectsTask_content[project.name] = null;
          context.scope.data.projectsTask_message[project.name] = "_invalid";

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "12.9.3":
              context.rootScope.onUserTokenError();
              break;

              case "12.9.9":
              context.scope.data.projectsTask_message[project.name] = "_denied";
              break;

              default:
              context.scope.data.projectsTask_message[project.name] = "_invalid";
              break;
            }
        });
    }, context);
  };


  $http.get($rootScope.apiBaseURL + '/dashboard/getprojectlist/' + $cookies.get('USERTOKEN'))
    .then(function projectsReceived(response) {
      $scope.data.projects = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      $scope.data.isValid = true;
      $scope.data.onLoad = false;

      getTasksContent();
    },
    function projectsNotReceived(response) {
      $scope.data.projects = null;
      $scope.data.isValid = false;
      $scope.data.onLoad = false;
    });


  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  // Tags in string format
  $scope.formatTagsinString = function(tags) {
    var tagsInString = "";

    for(var i = 0; i < tags.length; ++i) {
      tagsInString += (i != 0 ? ", " : "") + tags[i].name;
    }
    if (tags.length <= 0)
      tagsInString = "N/A";
    return tagsInString;
  };

  // Users in string format
  $scope.formatUsersinString = function(users) {
    var usersInString = "";

    for(var i = 0; i < users.length; ++i) {
      usersInString += (i != 0 ? ", " : "") + users[i].firstname + " " + users[i].lastname;
    }
    if (users.length <= 0)
      usersInString = "N/A";
    return usersInString;
  };

  // $scope.getTasks = function() {
  //   getTasksContent();
  // };

  $scope.displayTasks = function() {
    getTasksContent();
  };

  $scope.displayGantt = function() {
    getTasksContent();
  };

  $scope.openTask = function(project, task){
    $location.path('/tasks/' + project + '/' + task);
  }

}]);



/**
* Routine definition
* APP task page access
*
*/

// Routine definition [3/3]
// Common behavior for isTaskAccessible
var isTaskAccessible_commonBehavior = function(deferred, $location) {
  deferred.reject();
  $location.path("tasks");
};

// Routine definition [2/3]
// Default behavior for isWTaskAccessible
var isTaskAccessible_defaultBehavior = function(deferred, $location) {
  isTaskAccessible_commonBehavior(deferred, $location);
  Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
};

// Routine definition [1/3]
// Check if requested task is accessible
var isTaskAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  if ($route.current.params.id == 0)
  {
    deferred.resolve();
    return deferred.promise;
  }

  $http.get($rootScope.apiBaseURL + '/tasks/taskinformations/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function successCallback(response) {
      deferred.resolve();
    },
    function errorCallback(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "12.1.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "12.1.4":
          isTaskAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "Task not found.", delay: 10000 });
          break;

          case "12.1.9":
          isTaskAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "You don\'t have access to this task.", delay: 10000 });
          break;

          default:
          isTaskAccessible_defaultBehavior(deferred, $location);
          break;
        }
      }
      else { isTaskAccessible_defaultBehaviorv(deferred, $location); }
    });

    return deferred.promise;
};

// "isTaskAccessible" routine injection
isTaskAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];
