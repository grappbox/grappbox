/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP tasks list
app.controller("TaskListController", ["$http", "$filter", "$location", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope",
    function($http, $filter, $location, notificationFactory, $rootScope, $route, $routeParams, $scope) {

  var content = "";

  // Scope variables initialization
  $scope.projectId = $routeParams.project_id;
  $scope.data = { onLoad: true, tasks: { }, todos: [], doings: [], dones: [], users: [], containers: [], milestones: [], message: "_invalid" };

  // Get all tasks of the project
  $http.get($rootScope.api.url + "/tasks/project/" + $scope.projectId, {headers: { 'Authorization': $rootScope.user.token }})
    .then(function projectsReceived(response) {
      $scope.data.tasks = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
      $scope.data.onLoad = false;
      filterTasks();
    },
    function projectsNotReceived(response) {
      $scope.data.tasks = null;
      $scope.data.onLoad = false;

      if (response.data.info && response.data.info.return_code)
        switch(response.data.info.return_code) {
          case "12.14.3":
          $rootScope.reject();
          break;

          case "12.14.9":
          $scope.data.message = "_denied";
          break;

          default:
          $scope.data.message = "_invalid";
          break;
        }

    });

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "-");
  };

  // Tags in string format
  $scope.formatTagsinString = function(tags) {
    var tagsInString = "";

    for(var i = 0; i < tags.length; ++i) {
      tagsInString += (i != 0 ? ", " : "") + tags[i].name;
    }
    if (tags.length <= 0)
      tagsInString = "-";
    return tagsInString;
  };

  // Users in string format
  $scope.formatUsersinString = function(users) {
    var usersInString = "";

    for(var i = 0; i < users.length; ++i) {
      usersInString += (i != 0 ? ", " : "") + users[i].firstname + " " + users[i].lastname;
    }
    if (users.length <= 0)
      usersInString = "-";
    return usersInString;
  };

  // Open task detail page
  $scope.openTask = function(project, task){
    $location.path("/tasks/" + project + "/" + task);
  };

  /*-------------------------TAB FILTERS AND SWITCH ----------------------*/
  $scope.displayTasks = function(type) {
    switch (type) {
      case 'todo':
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-todo")[0].classList.add("active");
        $("#task-todo-list")[0].classList.add("active");
        break;
      case 'doing':
        $("#task-todo")[0].classList.remove("active");
        $("#task-todo-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-doing")[0].classList.add("active");
        $("#task-doing-list")[0].classList.add("active");
        break;
      case 'done':
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-todo")[0].classList.remove("active");
        $("#task-todo-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-done")[0].classList.add("active");
        $("#task-done-list")[0].classList.add("active");
        break;
      case 'user':
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-todo")[0].classList.remove("active");
        $("#task-todo-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-user")[0].classList.add("active");
        $("#task-user-list")[0].classList.add("active");
        break;
      case 'container':
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-todo")[0].classList.remove("active");
        $("#task-todo-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-container")[0].classList.add("active");
        $("#task-container-list")[0].classList.add("active");
        break;
      case 'milestone':
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-todo")[0].classList.remove("active");
        $("#task-todo-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");

        $("#task-milestone")[0].classList.add("active");
        $("#task-milestone-list")[0].classList.add("active");
        break;

      default:
        $("#task-doing")[0].classList.remove("active");
        $("#task-doing-list")[0].classList.remove("active");
        $("#task-done")[0].classList.remove("active");
        $("#task-done-list")[0].classList.remove("active");
        $("#task-user")[0].classList.remove("active");
        $("#task-user-list")[0].classList.remove("active");
        $("#task-container")[0].classList.remove("active");
        $("#task-container-list")[0].classList.remove("active");
        $("#task-milestone")[0].classList.remove("active");
        $("#task-milestone-list")[0].classList.remove("active");

        $("#task-todo")[0].classList.add("active");
        $("#task-todo-list")[0].classList.add("active");
        break;
    }
  };

  // $scope.filterTodo = function (item) {
  //     return (!item.is_milestone && !item.is_container && (!item.advance || item.advance == 0));
  // };
  //
  // $scope.filterDoing = function (item) {
  //     return (!item.is_milestone && !item.is_container && item.advance && item.advance > 0 && item.advance < 100);
  // };
  //
  // $scope.filterDone = function (item) {
  //     return (!item.is_milestone && !item.is_container && item.advance && item.advance == 100);
  // };
  //
  // $scope.filterUser = function (item) {
  //     return $filter('filter')(item.users_assigned, {id: $rootScope.user.id})[0];
  // };
  //
  // $scope.filterContainer = function (item) {
  //   return (item.is_container);
  // };
  //
  // $scope.filterMilestone = function (item) {
  //   return (item.is_milestone);
  // };

  var filterTasks = function() {
    angular.forEach($scope.data.tasks, function (item) {
      if (item.is_milestone)
        $scope.data.milestones.push(item);
      else if (item.is_container)
        $scope.data.containers.push(item);
      else {
        if (!item.advance || item.advance == 0) {
          $scope.data.todos.push(item);
        }
        else if (item.advance && item.advance == 100) {
          $scope.data.dones.push(item);
        }
        else {
          $scope.data.doings.push(item);
        }
        if ($filter('filter')(item.users, {id: $rootScope.user.id})[0]) {
          $scope.data.users.push(item);
        }
      }
    });
  };

}]);
