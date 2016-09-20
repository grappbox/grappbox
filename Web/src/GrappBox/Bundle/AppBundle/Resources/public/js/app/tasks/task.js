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
app.filter('range', function() {
  return function(input, total) {
      total = parseInt(total);

      for (var i=0; i<total; i++) {
        input.push(i);
      }

      return input;
    };
});

app.directive('convertToNumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(val) {
        return parseInt(val, 10);
      });
      ngModel.$formatters.push(function(val) {
        return '' + val;
      });
    }
  };
});

app.controller("taskController", ["$rootScope", "$scope", "$routeParams", "$http", "Notification", "$route", "$location", function($rootScope, $scope, $routeParams, $http, Notification, $route, $location) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.projectName = $routeParams.projectName;
  $scope.taskID = $routeParams.id;

  $scope.data = { onLoad: true, task_new: false, canEdit: true, task: { }, toUpdate: { }, tasks: [], tags: [], users: [], message: "_invalid" };

  $scope.dependenciesList = [{key: "fs", name: "finish to start"},
                             {key: "sf", name: "start to finish"},
                             {key: "ff", name: "finish to finish"},
                             {key: "ss", name: "start to start"}];


  // Get the task informations
  var getTask = function() {
    //Get task informations if not new
    if ($scope.taskID != 0) {
      $http.get($rootScope.api.url + "/tasks/taskinformations/" + $rootScope.user.token + "/" + $scope.taskID)
        .then(function successCallback(response) {
          $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
          $scope.data.onLoad = false;
          formatTasksforTagInput();
        },
        function errorCallback(response) {
          $scope.data.task = null;
          $scope.data.onLoad = false;

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "12.10.3":
              $rootScope.onUserTokenError();
              break;

              case "12.10.9":
              $scope.data.message = "_denied";
              break;

              default:
              $scope.data.message = "_invalid";
              break;
            }
        });
    }
    else {
      $scope.data.task_new = true;
      $scope.data.message = '_valid';
      $scope.data.task = [];
      $scope.data.task.type = "regular";
      $scope.data.task.color = "#44BBFF";
      $scope.data.task.users_assigned = [];
      $scope.data.task.dependencies = [];
      $scope.data.onLoad = false;
    }
  };
  getTask();

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
    $http.get($rootScope.api.url + "/tasks/getprojecttasks/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function successCallback(response) {
        $scope.tasksList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.tasksList, function(task){
          task["name"] = task.title;
        });
      },
      function errorCallback(response) {
        $scope.tasksList = [];
      });
  };
  getProjectTasks();


  // ------------------------------------------------------//
  //                    DISPLAY HELP                       //
  // ------------------------------------------------------//

  //-----------------DATE FORMATING----------------------//
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(" ")) : "N/A");
  };

  //----------------TASKS FORMATING---------------------//
  var formatTasksforTagInput = function() {
    angular.forEach($scope.data.task.tasks, function(task) {
      task['name'] = task.title;
    });
  };

  //------------------EDITION SWITCH-----------------------//
  $scope.data.editMode = {};
  $scope.data.editMode["task"] = false;

  var setEditableContent = function(){
    $scope.data.toUpdate.title = $scope.data.task.title;
    $scope.data.toUpdate.description = $scope.data.task.description;
    $scope.data.toUpdate.color = $scope.data.task.color;
    $scope.data.toUpdate.type = ($scope.data.task.is_milestone ? 'milestone' : ($scope.data.task.is_container ? 'container': 'regular'));
    $scope.data.toUpdate.started_at = ($scope.data.task.started_at ? new Date($scope.data.task.started_at.date) : null);
    $scope.data.toUpdate.due_date = ($scope.data.task.due_date ? new Date($scope.data.task.due_date.date) : null);
    $scope.data.toUpdate.advance = $scope.data.task.advance;
  };

  $scope.task_switchEditMode = function(elem) {
    setEditableContent();
    $scope.data.editMode[elem] = ($scope.data.editMode[elem] ? false : true);
  };

  //------------------GENERATE NUMBER LOOP-----------------------//
  $scope.number = 10;
  $scope.getNumber = function(num) {
    return new Array(num);
  };

  //-----------------INT CONVERT FOR SELECT---------------------//
  $scope.convertToInt = function(nb){
    return parseInt(nb, 10);
  };

  //----------------DEPENDENCY TYPE TO STRING-------------------//
  $scope.dependencyToString = function(dep){
    return (dep == "fs" ? "finish to start" : (dep == "sf" ? "start to finish" : (dep == "ff" ? "finish to finish" : (dep == "ss" ? "start to start" : "N/A"))));
  };

  // ------------------------------------------------------//
  //            REGULAR TASK MANAGEMENT                    //
  // ------------------------------------------------------//

  //----------------TAGS ASSIGNATION----------------------//
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
        var data = {"data": {"token": $rootScope.user.token, "projectId": $scope.projectID, "name": tag.name}};
        $http.post($rootScope.api.url + "/tasks/tagcreation", data)
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);
          },
          function errorCallback(response) {
              Notification.warning({ message: "Unable to create tag: " + tag.name + ". Please try again.", delay: 5000 });
          });
      }

      var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "tagId": tag.id}};
      $http.put($rootScope.api.url + "/tasks/assigntagtotask", data)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: "Unable to assign tag: " + tag.name + ". Please try again.", delay: 5000 });
        });
    });

    angular.forEach($scope.tagToRemove, function(tag) {
      $http.delete($rootScope.api.url + "/tasks/removetagtotask/" + $rootScope.user.token + "/" + $scope.taskID + "/" + tag.id)
        .then(function successCallback(response) {

        },
        function errorCallback(response) {
            Notification.warning({ message: "Unable to remove tag: " + tag.name + ". Please try again.", delay: 5000 });
        });
    });
  };

  //----------------TASKS ASSIGNATION----------------------//
  $scope.data.taskToAdd = [];
  $scope.data.taskToRemove = [];

  $scope.taskAdded = function(task) {
    var index = -1;
    for (var i = 0; i < $scope.data.taskToRemove.length && index < 0; i++) {
      if ($scope.data.taskToRemove[i] == task.id || $scope.data.taskToRemove[i] == task.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.taskToRemove.splice(index, 1);
    else
      $scope.data.taskToAdd.push(task.id);
  };

  $scope.taskRemoved = function(task) {
    var index = -1;
    for (var i = 0; i < $scope.data.taskToAdd.length && index < 0; i++) {
      if ($scope.data.taskToAdd[i] == task.id || $scope.data.taskToAdd[i] == task.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.taskToAdd.splice(index, 1);
    else
      $scope.data.taskToRemove.push(task.id);
  };

  // var memorizeTasks = function() {
  //   var toAdd = [];
  //   angular.forEach($scope.taskToRemove, function(user) {
  //     toAdd.push(task.id);
  //   }, toAdd);
  //   var toRemove = [];
  //   angular.forEach($scope.taskToRemove, function(user) {
  //     toRemove.push(task.id);
  //   }, toRemove);
  //
  //   var data = {"data": {"token": $rootScope.user.token, "bugId": $scope.ticketID, "tasksAdd": toAdd, "tasksRemove": toRemove}};
  //
  //   $http.put($rootScope.api.url + '/bugtracker/setparticipants', data)
  //     .then(function successCallback(response) {
  //
  //     },
  //     function errorCallback(resposne) {
  //         Notification.warning({ message: 'Unable to save users. Please try again.', delay: 5000 });
  //     });
  //
  // };

  //-----------------USERS ASSIGNATION--------------------//
  $scope.addUser = function(assign_user, workcharge) {
    if (assign_user == "")
      return;
    else if (workcharge == "") {
      Notification.warning({ message: "You must select a workcharge before adding the user.", delay: 5000 });
    }

    var user = JSON.parse(assign_user);

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.users_assigned.length && index < 0; i++) {
        if ($scope.data.task.users_assigned[i].id == user.user_id)
          index = i;
      }
      if (index >= 0) {
        Notification.warning({ message: "User already assigned.", delay: 5000 });
        return;
      }
      $scope.data.task.users_assigned.push({id: user.user_id, firstname: user.first_name, lastname: user.last_name, percent: workcharge});
      return;
    }

    var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "userId": user.user_id, "percent": workcharge}};

    $http.put($rootScope.api.url + "/tasks/assignusertotask", data)
      .then(function successCallback(response) {
          getTask();
          Notification.success({ message: "User added", delay: 5000 });
      },
      function errorCallback(response) {
          Notification.warning({ message: "Unable to add user. Please try again.", delay: 5000 });
      });
  };

  $scope.updateUser = function(assign_user) {
    return ;
  };

  $scope.removeUser = function(assign_user) {
    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.users_assigned.length && index < 0; i++) {
        if ($scope.data.task.users_assigned[i].id == assign_user.id)
          index = i;
      }

      if (index >= 0) {
        $scope.data.task.users_assigned.splice(index, 1);
        //getTask();
      }
      return;
    }

    $http.delete($rootScope.api.url + "/tasks/removeusertotask/"+$rootScope.user.token+'/'+$scope.taskID+'/'+assign_user.id)
      .then(function successCallback(response) {
          Notification.success({ message: "User removed", delay: 5000 });
          getTask();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to remove user. Please try again.", delay: 5000 });
      });
  };

  var assignUsers = function(task) {
    angular.forEach(task.users_assigned, function(value, key) {
      var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "userId": value.id, "percent": value.percent}};

      $http.put($rootScope.api.url + "/tasks/assignusertotask", data)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: "Unable to add user. Please try again.", delay: 5000 });
        });
    });
  };

  //-----------------DEPENDENCIES ASSIGNATION--------------------//
  $scope.addDependency = function(dep, type) {
    if (dep == "")
      return;
    else if (type == "") {
      Notification.warning({ message: "You must select a type of dependency before adding the dependency.", delay: 5000 });
    }

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
        if ($scope.data.task.dependencies[i].id == dep.id)
          index = i;
      }
      if (index >= 0) {
        Notification.warning({ message: "Dependency already existing.", delay: 5000 });
        return;
      }
      $scope.data.task.dependencies.push({id: dep.id, name: type, title: dep.title});
      return;
    }

    var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "dependencies": [{"id": dep.id, "name": type}]}};

    $http.put($rootScope.api.url + "/tasks/taskupdate", data)
      .then(function successCallback(response) {
          getTask();
          Notification.success({ message: "Dependency added", delay: 5000 });
      },
      function errorCallback(response) {
          Notification.warning({ message: "Unable to add dependency. Please try again.", delay: 5000 });
      });
  };

  $scope.updateDependency = function(dep) {
    // var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "dependencies": [{"id": dep.id, "name": dep.name}]}};
    //
    // $http.put($rootScope.api.url + "/tasks/taskupdate", data)
    //   .then(function successCallback(response) {
    //       getTask();
    //       Notification.success({ message: "Dependency updated", delay: 5000 });
    //   },
    //   function errorCallback(response) {
    //       Notification.warning({ message: "Unable to update dependency. Please try again.", delay: 5000 });
    //   });
    return;
  };

  $scope.removeDependency = function(dependency) {
    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
        if ($scope.data.task.dependencies[i].id == dependency.id)
          index = i;
      }

      if (index >= 0) {
        $scope.data.task.dependencies.splice(index, 1);
        //getTask();
      }
      return;
    }

    var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "dependenciesRemove": [dependency.id]}};

    $http.put($rootScope.api.url + "/tasks/taskupdate", data)
      .then(function successCallback(response) {
          getTask();
          Notification.success({ message: "Dependency removed", delay: 5000 });
      },
      function errorCallback(response) {
          Notification.warning({ message: "Unable to remove dependency. Please try again.", delay: 5000 });
      });

  };

  // ------------------------------------------------------
  //                    TASK
  // ------------------------------------------------------

  $scope.createTask = function(task) {
    Notification.info({ message: "Posting task...", delay: 5000 });
    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "title": task.title,
                "description": task.description,
                "due_date": new Date(task.due_date),
                "is_milestone": false,
                "is_container": false
                };
    if (task.type == "container") {
      elem['is_container'] = true;
      elem["tasksAdd"] = $scope.data.taskToAdd;
      elem["tasksRemove"] = $scope.data.taskToRemove;
    }
    if (task.type == "milestone") {
      elem['is_milestone'] = true;
      elem['dependencies'] = task.dependencies;
    }
    if (task.type == "regular") {
      elem['dependencies'] = task.dependencies;
    }

    var data = {"data": elem};

    $http.post($rootScope.api.url + "/tasks/taskcreation", data)
      .then(function successCallback(response) {
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.taskID = $scope.data.task.id;
        $scope.data.message = '_valid';
        memorizeTags();
        assignUsers(task);
        Notification.success({ message: "Task posted", delay: 5000 });
        $location.path("/task/" + $scope.projectID + "/" + $scope.taskID);
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to post task. Please try again.", delay: 5000 });
      }, $scope);
  };

  $scope.editTask = function(task) {
    Notification.info({ message: "Saving task...", delay: 5000 });

    var elem = {"token": $rootScope.user.token,
                "taskId": $scope.taskID,
                "title": $scope.data.toUpdate.title,
                "description": $scope.data.toUpdate.description,
                //"due_date": new Date($scope.data.toUpdate.due_date),
                "is_milestone": false,
                "is_container": false,
                "advance": $scope.data.toUpdate.advance
                };
    if ($scope.data.toUpdate.started_at.getTime() != (new Date($scope.data.task.started_at.date).getTime()))
      elem['started_at'] = $scope.data.toUpdate.started_at;

    if ($scope.data.toUpdate.type == "container") {
      elem['is_container'] = true;
      elem["tasksAdd"] = $scope.data.taskToAdd;
      elem["tasksRemove"] = $scope.data.taskToRemove;
    }
    if ($scope.data.toUpdate.type == "milestone") {
      elem['is_milestone'] = true;
      //elem['dependencies'] = [];
    }
    if ($scope.data.toUpdate.type == "regular") {
      //elem['dependencies'] = [];
    }
    if ($scope.data.toUpdate.advance == 100)
      elem['finished_at'] = new Date();
    else if ($scope.data.toUpdate.advance < 100 && $scope.data.task.finished_at)
      elem['finished_at'] = null;
    else if ($scope.data.toUpdate.due_date.getTime() != (new Date($scope.data.task.due_date.date).getTime()))
      elem['due_date'] = $scope.data.toUpdate.due_date;

    var data = {"data": elem};

    $http.put($rootScope.api.url + "/tasks/taskupdate", data)
      .then(function successCallback(response) {
        memorizeTags();
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        Notification.success({ message: "Task saved", delay: 5000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to save task. Please try again.", delay: 5000 });
      });
      $scope.data.editMode["task"] = false;
  };

  $scope.finishTask = function() {
    var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "advance": 100, "finished_at": new Date()}};

    $http.put($rootScope.api.url + "/tasks/taskupdate", data)
      .then(function successCallback(response) {
        memorizeTags();
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        Notification.success({ message: "Task saved", delay: 5000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to save task. Please try again.", delay: 5000 });
      });
  };

  $scope.deleteTask = function() {
    Notification.info({ message: "Closing task ...", delay: 5000 });
    $http.delete($rootScope.api.url + "/tasks/closetask/" + $rootScope.user.token + "/" + $scope.ticketID)
      .then(function successCallback(response) {
          Notification.success({ message: "Task closed", delay: 5000 });
          $route.reload();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to close task. Please try again.", delay: 5000 });
      });
  };


}]);
