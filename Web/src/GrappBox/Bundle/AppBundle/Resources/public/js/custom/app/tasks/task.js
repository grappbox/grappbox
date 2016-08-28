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

  $scope.defaultColor = "";

  // Get the task informations
  var getTask = function() {
    //Get task informations if not new
    if ($scope.taskID != 0) {
      $http.get($rootScope.api.url + "/tasks/taskinformations/" + $rootScope.user.token + "/" + $scope.taskID)
        .then(function successCallback(response) {
          $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
          $scope.data.onLoad = false;
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
      $scope.data.task.task_type = "regular";
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

  //------------------EDITION SWITCH-----------------------//
  $scope.data.editMode = {};
  $scope.data.editMode["task"] = false;

  var setEditableContent = function(){
    $scope.data.toUpdate.title = ($scope.data.task.title && $scope.data.task.title != "" ? $scope.data.task.title : null);
    $scope.data.toUpdate.description = ($scope.data.task.description && $scope.data.task.description != "" ? $scope.data.task.description : null);
    $scope.data.toUpdate.type = ($scope.data.task.type && $scope.data.task.type != "" ? $scope.data.task.type : "regular");
    $scope.data.toUpdate.started_at = ($scope.data.task.started_at ? new Date($scope.data.task.started_at.date) : null);
    $scope.data.toUpdate.due_date = ($scope.data.task.due_date ? new Date($scope.data.task.due_date.date) : null);
    $scope.data.toUpdate.progress = ($scope.data.task.progress && $scope.data.task.progress != "" ? $scope.data.task.progress : "-1");
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

  //-----------------USERS ASSIGNATION--------------------//
  var addUser = function(assign_user) {
    var data = {"data": {"token": $rootScope.user.token, "taskId": $scope.taskID, "userId": assign_user.id, "percent": assign_user.percent}};

    $http.put($rootScope.api.url + "/tasks/assignusertotask", data)
      .then(function successCallback(response) {
          Notification.success({ message: "User added", delay: 5000 });
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to add user. Please try again.", delay: 5000 });
      });
  };

  var updateUser = function(assign_user) {
    return ;
  };

  var removeUser = function(assign_user) {
    $http.delete($rootScope.api.url + "/tasks/removeusertotask/"+$rootScope.user.token+'/'+$scope.taskID+'/'+assign_user.id, data)
      .then(function successCallback(response) {
          Notification.success({ message: "User removed", delay: 5000 });
      },
      function errorCallback(resposne) {
          Notification.warning({ message: "Unable to remove user. Please try again.", delay: 5000 });
      });
  };

  //-----------------DEPENDENCIES ASSIGNATION--------------------//
  var addDependency = function(dependency) {
    return ;
  };

  var updateDependency = function(dependency) {
    return ;
  };

  var removeDependency = function(dependency) {
    return ;
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
                "color": task.color,
                "due_date": task.due_date,
                "is_milestone": false,
                "is_container": false
                };
    if (task.type == "container") {
      elem['is_container'] = true;
      elem['tasksAdd'] = [];
    }
    if (task.type == "milestone") {
      elem['is_milestone'] = true;
      elem['dependencies'] = [];
    }
    if (task.type == "regular") {
      elem['dependencies'] = [];
    }

    var data = {"data": elem};

    $http.post($rootScope.api.url + "/tasks/posttask", data)
      .then(function successCallback(response) {
        $scope.taskID = $scope.task.id;
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.message = '_valid';
        memorizeTags();
        // TODO : update user assignment
        Notification.success({ message: "Task posted", delay: 5000 });
        $location.path("/tasks/" + $scope.projectID + "/" + $scope.taskID);
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to post task. Please try again.", delay: 5000 });
      }, $scope);
  };

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
        //memorizeTags();
        //memorizeUsers();
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to save task. Please try again.", delay: 5000 });
      });
      $scope.editMode["task"] = false;
  };

  $scope.archiveTask = function() {
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
