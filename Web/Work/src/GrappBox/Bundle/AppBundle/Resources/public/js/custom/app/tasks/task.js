/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP task page
*
*/
app.controller("taskController", ["$rootScope", "$scope", "$routeParams", "$http", "Notification", "$route", "$location", function($rootScope, $scope, $routeParams, $http, Notification, $route, $location) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.projectName = $routeParams.projectName;
  $scope.taskID = $routeParams.id;

  $scope.data = { onLoad: true, ticket: { }, message: "_invalid" };

  //Get task informations if not new
  if ($scope.taskID != 0) {
    $http.get($rootScope.api.url + "/tasks/taskinformations/" + $rootScope.user.token + "/" + $scope.taskID)
      .then(function successCallback(response) {
        $scope.task_new = false;
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.??.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
      },
      function errorCallback(response) {
        $scope.task_error = true;
        $scope.task_new = false;
        $scope.task = null;
      });
  }
  else {
    $scope.task_error = false;
    $scope.task = null;
    $scope.task_new = true;
    $scope.task = [];
    $scope.task.task_type = "regular";
    $scope.tags = [];
    $scope.users = [];
  }

  // Get all tags from the project
  var getProjectTags = function() {
    $http.get($rootScope.api.url + "/tasks/getprojecttags/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function successCallback(response) {
        $scope.tagsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      },
      function errorCallback(response) {
        $scope.tagsList = [];
      });
  };
  getProjectTags();

  // Get all users from the project
  var getProjectUsers = function() {
    $http.get($rootScope.api.url + "/dashboard/getprojectpersons/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function successCallback(response) {
        $scope.usersList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.usersList, function(user){

          user["name"] = user.first_name + " " + user.last_name;

        });
      },
      function errorCallback(response) {
        $scope.usersList = [];
      });
  };
  getProjectUsers();

  // Get all tasks from the project
  var getProjectTasks = function() {
    $http.get($rootScope.api.url + "/tasks/gettasksinformations/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function successCallback(response) {
        $scope.tasksList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.tasksList, function(task){

          user["name"] = task.title;

        });
      },
      function errorCallback(response) {
        $scope.tasksList = [];
      });
  };
  getProjectTasks();

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };


  // ------------------------------------------------------
  //                 TAGS ASSIGNATION
  // ------------------------------------------------------
  $scope.tagToAdd = [];
  $scope.tagToRemove = [];

  $scope.tagAdded = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.tagToRemove.length && index < 0; i++) {
      if ($scope.tagToRemove[i].id == tag.id)
        index = i;
    }

    if (index >= 0)
      $scope.tagToRemove.splice(index, 1);
    else
      $scope.tagToAdd.push(tag);
  };

  $scope.tagRemoved = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.tagToAdd.length && index < 0; i++) {
      if ($scope.tagToAdd[i].id == tag.id)
        index = i;
    }
    if (index >= 0)
      $scope.tagToAdd.splice(index, 1);
    else
      $scope.tagToRemove.push(tag);
  };

  var memorizeTags = function() {
    var context = {"rootScope": $rootScope, "http": $http, "Notification": Notification, "scope": $scope};

    angular.forEach($scope.tagToAdd, function(tag) {
      if (!tag.id) {
        var data = {"data": {"token": context.rootScope.user.token, "projectId": context.scope.projectID, "name": tag.name}};
        context.http.post(context.rootScope.api.url + "/tasks/tagcreation", data)
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);
          },
          function errorCallback(resposne) {
              Notification.warning({ message: "Unable to create tag: " + tag.name + ". Please try again.", delay: 5000 });
          });
      }

      var data = {"data": {"token": context.rootScope.user.token, "bugId": context.scope.taskID, "tagId": tag.id}};
      context.http.put(context.rootScope.api.url + "/tasks/assigntag", data)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: "Unable to assign tag: " + tag.name + ". Please try again.", delay: 5000 });
        });
    }, context);

    angular.forEach($scope.tagToRemove, function(tag) {
      context.http.delete(context.rootScope.api.url + "/tasks/removetag/" + context.rootScope.user.token + "/" + context.scope.taskID + "/" + tag.id)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: "Unable to remove tag: " + tag.name + ". Please try again.", delay: 5000 });
        });
    }, context);
  };

  // ------------------------------------------------------
  //                 USERS ASSIGNATION
  // ------------------------------------------------------
  $scope.userToAdd = [];
  $scope.userToRemove = [];

  $scope.userAdded = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.userToRemove.length && index < 0; i++) {
      if ($scope.userToRemove[i].user_id == user.user_id || $scope.userToRemove[i].id == user.user_id)
        index = i;
    }
    if (index >= 0)
      $scope.userToRemove.splice(index, 1);
    else
      $scope.userToAdd.push(user);
  };

  $scope.userRemoved = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.userToAdd.length && index < 0; i++) {
      if ($scope.userToAdd[i].user_id == user.user_id || $scope.userToAdd[i].user_id == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.userToAdd.splice(index, 1);
    else
      $scope.userToRemove.push(user);
  };

  var memorizeUsers = function() {
    var toAdd = [];
    angular.forEach($scope.userToAdd, function(user) {
      toAdd.push(user.user_id);
    }, toAdd);
    var toRemove = [];
    angular.forEach($scope.userToRemove, function(user) {
      toRemove.push(user.id);
    }, toRemove);

    var data = {"data": {"token": $rootScope.user.token, "bugId": $scope.taskID, "toAdd": toAdd, "toRemove": toRemove}};

    Notification.info({ message: "Saving users...", delay: 5000 });
    $http.put($rootScope.api.url + "/tasks/setparticipants", data)
      .then(function successCallback(response) {
          Notification.success({ message: "Users saved", delay: 5000 });
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to save users. Please try again.", delay: 5000 });
      });

  };

  // ------------------------------------------------------
  //                 TASK ASSIGNATION
  // ------------------------------------------------------
  $scope.taskToAdd = [];
  $scope.taskToRemove = [];

  $scope.taskAdded = function(task) {
    var index = -1;
    for (var i = 0; i < $scope.taskToRemove.length && index < 0; i++) {
      if ($scope.taskToRemove[i].task_id == task.task_id || $scope.taskToRemove[i].id == task.task_id)
        index = i;
    }
    if (index >= 0)
      $scope.taskToRemove.splice(index, 1);
    else
      $scope.taskToAdd.push(task);
  };

  $scope.taskRemoved = function(task) {
    var index = -1;
    for (var i = 0; i < $scope.taskToAdd.length && index < 0; i++) {
      if ($scope.taskToAdd[i].task_id == task.task_id || $scope.taskToAdd[i].task_id == task.id)
        index = i;
    }
    if (index >= 0)
      $scope.taskToAdd.splice(index, 1);
    else
      $scope.taskToRemove.push(task);
  };

  var memorizeTasks = function() {
    var toAdd = [];
    angular.forEach($scope.taskToAdd, function(task) {
      toAdd.push(task.task_id);
    }, toAdd);
    var toRemove = [];
    angular.forEach($scope.taskToRemove, function(task) {
      toRemove.push(task.id);
    }, toRemove);

    var data = {"data": {"token": $rootScope.user.token, "bugId": $scope.taskID, "toAdd": toAdd, "toRemove": toRemove}};

    // Notification.info({ message: "Saving users...", delay: 5000 });
    // $http.put($rootScope.api.url + "/tasks/setparticipants", data)
    //   .then(function successCallback(response) {
    //       Notification.success({ message: "Users saved", delay: 5000 });
    //   },
    //   function errorCallback(resposne) {
    //       Notification.warning({ message: "Unable to save users. Please try again.", delay: 5000 });
    //   });
    return data;
  };


  // ------------------------------------------------------
  //                EDITION SWITCH
  // ------------------------------------------------------
  $scope.editMode = {};
  $scope.editMode["task"] = false;

  $scope.task_switchEditMode = function(elem) {
    $scope.editMode[elem] = ($scope.editMode[elem] ? false : true);
  };

  // ------------------------------------------------------
  //                    TASK
  // ------------------------------------------------------
  $scope.editTask = function(task) {
    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "title": task.title,
                "description": task.description,
                //"color": task.color,
                "due_date": task.due_date,
                "is_milestone": task.is_milestone,
                "is_container": task.is_container//,
                //"tasksAdd": task.tasksAdd,
                //"tasksRemove": task.tasksRemove,
                //"dependencies": task.dependencies,
                //"started_at": task.started_at,
                //"finished_at": task.finished_at,
                //"advance": task.advance
                };
    var data = {"data": elem};

    Notification.info({ message: "Saving task...", delay: 5000 });
    $http.put($rootScope.api.url + "/tasks/edittask", data)
      .then(function successCallback(response) {
        Notification.success({ message: "Task saved", delay: 5000 });
        memorizeTags();
        memorizeUsers();
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to save task. Please try again.", delay: 5000 });
      });
      $scope.editMode["task"] = false;
  };

  $scope.createTask = function(task) {
    var container = false;
    var milestone = false;
    if (task.task_type == "container")
      container = true;
    else if (task.task_type == "milestone")
      milestone = true;

    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "title": task.title,
                "description": task.description,
                //"color": task.color,
                "due_date": task.due_date,
                "is_milestone": milestone,
                "is_container": container
                //"tasksAdd": task.tasksAdd,
                //"tasksRemove": task.tasksRemove,
                //"dependencies": task.dependencies
                };
    var data = {"data": elem};

    Notification.info({ message: "Posting task...", delay: 5000 });
    $http.post($rootScope.api.url + "/tasks/posttask", data)
      .then(function successCallback(response) {
        $scope.task_error = false;
        $scope.task_new = false;
        $scope.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.taskID = $scope.task.id;
        memorizeTags();
        memorizeUsers();
        Notification.success({ message: "Task posted", delay: 5000 });
        $location.path("/tasks/" + $scope.projectID + "/" + $scope.taskID);
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to post task. Please try again.", delay: 5000 });
      }, $scope);
  };

  $scope.task_close_task = function() {
    Notification.info({ message: "Closing task ...", delay: 5000 });
    $http.delete($rootScope.api.url + "/tasks/closetask/" + $rootScope.user.token + "/" + $scope.ticketID)
      .then(function successCallback(response) {
          Notification.success({ message: "Task closed", delay: 5000 });
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to close task. Please try again.", delay: 5000 });
      });
  };


}]);
