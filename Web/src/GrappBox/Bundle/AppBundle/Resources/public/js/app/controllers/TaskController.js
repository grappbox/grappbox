/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// APP task filter
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

// Controller definition
// APP tasks
app.controller("TaskController", ["$http", "$filter", "$location", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope",
    function($http, $filter, $location, notificationFactory, $rootScope, $route, $routeParams, $scope) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.projectName = $routeParams.projectName;
  $scope.taskID = $routeParams.id;

  $scope.data = { onLoad: true, task_new: false, canEdit: true, task: { }, edit: { }, tasks: [], tags: [], users: [], message: "_invalid" };

  $scope.dependenciesList = [{key: "fs", name: "finish to start"},
                             {key: "sf", name: "start to finish"},
                             {key: "ff", name: "finish to finish"},
                             {key: "ss", name: "start to start"}];

  // Get the task informations
  var getTask = function() {
    //Get task informations if not new
    if ($scope.taskID != 0) {
      $http.get($rootScope.api.url + "/task/" + $scope.taskID, {headers: { 'Authorization': $rootScope.user.token }})
        .then(function successCallback(response) {
          $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
          $scope.data.onLoad = false;
          formatTasks();
          formatUsers();
          formatDependencies();
        },
        function errorCallback(response) {
          $scope.data.task = null;
          $scope.data.onLoad = false;

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "12.10.3":
              $rootScope.reject();
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
      //$scope.data.task = [];
      $scope.data.task.type = "regular";
      $scope.data.task.users = [];
      $scope.data.task.dependencies = [];
      $scope.data.onLoad = false;
    }
  };
  getTask();

  // Get all tags from the project
  var getProjectTags = function() {
    $http.get($rootScope.api.url + "/tasks/tags/project/" + $scope.projectID, {headers: { 'Authorization': $rootScope.user.token }})
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
    $http.get($rootScope.api.url + "/project/users/" + $scope.projectID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.usersList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.usersList, function(user){
          user["name"] = user.firstname + " " + user.lastname;
        });
      },
      function errorCallback(response) {
        $scope.usersList = [];
      });
  };
  getProjectUsers();

  // Get all tasks from the project
  var getProjectTasks = function() {
    $http.get($rootScope.api.url + "/tasks/project/" + $scope.projectID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.tasksList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : []);
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
    // TODO change date from GMT to local timezone
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(" ")) : "N/A");
  };

  //----------------TASKS FORMATING---------------------//
  var formatTasks = function() {
    angular.forEach($scope.data.task.tasks, function(task) {
      task['name'] = task.title;
    });
  };

  //----------------USERS FORMATING---------------------//
  var formatUsers = function() {
    angular.forEach($scope.data.task.users, function(user) {
      user["old"] = false;
      user["oldPercent"] = user.percent;
    });
  };

  //------------DEPENDENCIES FORMATING------------------//
  var formatDependencies = function() {
    angular.forEach($scope.data.task.dependencies, function(dep) {
      dep["old"] = false;
      dep["oldName"] = dep.name;
    });
  };

  //------------------EDITION SWITCH-----------------------//
  $scope.data.editMode = {};
  $scope.data.editMode["task"] = false;

  var setEditableContent = function(){
    $scope.tagToAdd = [];
    $scope.tagToRemove = [];
    $scope.data.taskToAdd = [];
    $scope.data.taskToRemove = [];
    $scope.data.edit.title = $scope.data.task.title;
    $scope.data.edit.description = $scope.data.task.description;
    $scope.data.edit.type = ($scope.data.task.is_milestone ? 'milestone' : ($scope.data.task.is_container ? 'container': 'regular'));
    $scope.data.edit.started_at = ($scope.data.task.started_at ? new Date($scope.data.task.started_at) : null);
    $scope.data.edit.due_date = ($scope.data.task.due_date ? new Date($scope.data.task.due_date) : null);
    $scope.data.edit.advance = $scope.data.task.advance;
    $scope.data.edit.newRes = [];
    $scope.data.edit.updateRes = [];
    $scope.data.edit.oldRes = [];
    $scope.data.edit.newDep = [];
    $scope.data.edit.updateDep = [];
    $scope.data.edit.oldDep = [];
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
    var context = {"rootScope": $rootScope, "http": $http, "notificationFactory": notificationFactory, "scope": $scope};

    angular.forEach($scope.tagToAdd, function(tag) {
      if (!tag.id) {
        var data = {"data": {"projectId": $scope.projectID, "name": tag.name, color: "#000"}}; //TODO add color
        $http.post($rootScope.api.url + "/tasks/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);
          },
          function errorCallback(response) {
              notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
          });
      }

      // TODO add and removetag in task update
      // var data = {"data": {"taskId": $scope.taskID, "tagId": tag.id}};
      // $http.put($rootScope.api.url + "/tasks/assigntagtotask", data, {headers: { 'Authorization': $rootScope.user.token }})
      //   .then(function successCallback(response) {
      //
      //   },
      //   function errorCallback(resposne) {
      //       notificationFactory.warning("Unable to assign tag: " + tag.name + ". Please try again.");
      //   });
    });

    angular.forEach($scope.tagToRemove, function(tag) {
      // $http.delete($rootScope.api.url + "/tasks/removetagtotask/" + $rootScope.user.token + "/" + $scope.taskID + "/" + tag.id)
      //   .then(function successCallback(response) {
      //
      //   },
      //   function errorCallback(response) {
      //       notificationFactory.warning("Unable to remove tag: " + tag.name + ". Please try again.");
      //   });
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

  //-----------------USERS ASSIGNATION--------------------//

  $scope.addResource = function(user, workcharge) {
    if (user == "")
      return ;
    else if (workcharge == "") {
      notificationFactory.warning("You must select a workcharge before adding the user.");
      return ;
    }

    var user = JSON.parse(user);

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
        if ($scope.data.task.users[i].id == user.id)
          index = i;
      }
      if (index >= 0) {
        notificationFactory.warning("User already assigned.");
        return;
      }
      $scope.data.task.users.push({id: user.id, firstname: user.firstname, lastname: user.lastname, percent: workcharge});
      return;
    }

    $scope.data.task.users.push({id: user.id, firstname: user.firstname, lastname: user.lastname, percent: workcharge});
    $scope.data.edit.newRes.push({"id": user.id, "percent": workcharge});
  };

  $scope.updateResource = function(user) {

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
        if ($scope.data.task.users[i].id == user.id)
          index = i;
      }
      if (index >= 0) {
        $scope.data.task.users[index].percent = workcharge;
        return;
      }
      notificationFactory.warning("Unable to update user.");
      return;
    }

    for (var i = 0; i < $scope.data.edit.newRes.length; i++) {
      if ($scope.data.edit.newRes[i].id == user.id) {
        $scope.data.edit.newRes[i].percent = user.percent;
        return ;
      }
    }

    for (var i = 0; i < $scope.data.edit.updateRes.length; i++) {
      if ($scope.data.edit.updateRes[i].id == user.id) {
        $scope.data.edit.updateRes[i].percent = user.percent;
        return ;
      }
    }

    var index = -1;
    for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
      if ($scope.data.task.users[i].id == user.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find resource for update.");
      return;
    }

    $scope.data.edit.updateRes.push({"id": user.id, "percent": user.percent})
  };

  $scope.removeResource = function(user) {

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
        if ($scope.data.task.users[i].id == user.id)
          index = i;
      }

      if (index >= 0) {
        $scope.data.task.users.splice(index, 1);
      }
      return;
    }

    var index = -1;
    for (var i = 0; i < $scope.data.edit.newRes.length && index < 0; i++) {
      if ($scope.data.edit.newRes[i].id == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.edit.newRes.splice(index, 1);

    var index = -1;
    for (var i = 0; i < $scope.data.edit.updateRes.length && index < 0; i++) {
      if ($scope.data.edit.updateRes[i].id == user.id)
        index = i;
    }
    if (index >= 0) {
      $scope.data.edit.updateRes.splice(index, 1);
      $scope.data.edit.oldRes.push(user.id);
    }

    var index = -1;
    for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
      if ($scope.data.task.users[i].id == user.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find resource to remove.");
      return;
    }
    $scope.data.task.users[index].old = true;
    $('#resource-'+index).addClass('old');
    $scope.data.edit.oldRes.push(user.id);
  };

  $scope.recoverResource = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.data.edit.oldRes.length && index < 0; i++) {
      if ($scope.data.edit.oldRes[i] == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.edit.oldRes.splice(index, 1);

    var index = -1;
    for (var i = 0; i < $scope.data.task.users.length && index < 0; i++) {
      if ($scope.data.task.users[i].id == user.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find resource to recover.");
      return;
    }
    $scope.data.task.users[index].old = false;
    $('#resource-'+index).removeClass('old');
    $scope.data.task.users[index].percent = $scope.data.task.users[index].oldPercent;
  }

  //-----------------DEPENDENCIES ASSIGNATION--------------------//
  $scope.addDependency = function(dep, type) {
    if (dep == "")
      return ;
    else if (type == "") {
      notificationFactory.warning("You must select a type of dependency before adding the dependency.");
      return ;
    }

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
        if ($scope.data.task.dependencies[i].id == dep.id)
          index = i;
      }
      if (index >= 0) {
        notificationFactory.warning("Dependency already existing.");
        return;
      }
      $scope.data.task.dependencies.push({id: dep.id, name: type, title: dep.title});
      return;
    }

    $scope.data.task.dependencies.push({"name": type, "task": {"title": dep.title}});
    $scope.data.edit.newDep.push({"id": dep.id, "name": type});
  };

  $scope.updateDependency = function(dep) {

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
        if ($scope.data.task.dependencies[i].id == dep.id)
          index = i;
      }
      if (index >= 0) {
        $scope.data.task.dependencies[index].name = dep.name;
        return;
      }
      notificationFactory.warning("Unable to update dependency.");
      return;
    }

    for (var i = 0; i < $scope.data.edit.newDep.length; i++) {
      if ($scope.data.edit.newDep[i].id == dep.id) {
        $scope.data.edit.newDep[i].name = dep.name;
        return ;
      }
    }

    for (var i = 0; i < $scope.data.edit.updateDep.length; i++) {
      if ($scope.data.edit.updateDep[i].id == dep.id) {
        $scope.data.edit.updateDep[i].name = dep.name;
        return ;
      }
    }

    var index = -1;
    for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
      if ($scope.data.task.dependencies[i].task.id == dep.task.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find dependency for update.");
      return;
    }

    $scope.data.edit.updateDep.push({"id": dep.task.id, "oldName": $scope.data.task.dependencies[index].oldName, "newName": dep.name})
  };

  $scope.removeDependency = function(dep) {

    if ($scope.data.task_new) {
      var index = -1;
      for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
        if ($scope.data.task.dependencies[i].id == dependency.id)
          index = i;
      }

      if (index >= 0) {
        $scope.data.task.dependencies.splice(index, 1);
      }
      return;
    }

    var index = -1;
    for (var i = 0; i < $scope.data.edit.newDep.length && index < 0; i++) {
      if ($scope.data.edit.newDep[i].id == dep.task.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.edit.newDep.splice(index, 1);

    var index = -1;
    for (var i = 0; i < $scope.data.edit.updateDep.length && index < 0; i++) {
      if ($scope.data.edit.updateDep[i].id == dep.task.id)
        index = i;
    }
    if (index >= 0) {
      $scope.data.edit.updateDep.splice(index, 1);
      $scope.data.edit.oldDep.push(dep.task.id);
    }

    var index = -1;
    for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
      if ($scope.data.task.dependencies[i].id == dep.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find dependency to remove.");
      return;
    }
    $('#dependency-'+index).addClass('old');
    $('#select-dependency-'+index).addClass('old');
    $scope.data.task.dependencies[index].old = true;
    $scope.data.edit.oldDep.push(dep.task.id);
  };

  $scope.recoverDependency = function(dep) {
    var index = -1;
    for (var i = 0; i < $scope.data.edit.oldDep.length && index < 0; i++) {
      if ($scope.data.edit.oldDep[i] == dep.task.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.edit.oldDep.splice(index, 1);

    var index = -1;
    for (var i = 0; i < $scope.data.task.dependencies.length && index < 0; i++) {
      if ($scope.data.task.dependencies[i].id == dep.id)
        index = i;
    }
    if (index < 0) {
      notificationFactory.warning("Unable to find dependency to recover.");
      return;
    }
    $('#dependency-'+index).removeClass('old');
    $('#select-dependency-'+index).removeClass('old');
    $scope.data.task.dependencies[index].old = false;
    $scope.data.task.dependencies[index].name = $scope.data.task.dependencies[index].oldName;
  }

  // ------------------------------------------------------
  //                    TASK
  // ------------------------------------------------------

  $scope.createTask = function(task) {

    var elem = {"projectId": $scope.projectID,
                "title": task.title,
                "description": task.description ? task.description : null,
                "is_milestone": false,
                "is_container": false,
                "due_date": $filter('date')(new Date(task.due_date), "yyyy-MM-dd H:mm:ss", "GMT"),
                "started_at": $filter('date')(new Date(task.started_at), "yyyy-MM-dd H:mm:ss", "GMT")
                };
    if (task.type == "container") {
      elem['is_container'] = true;
      elem["tasksAdd"] = $scope.data.taskToAdd;
      elem["tasksRemove"] = $scope.data.taskToRemove;
      elem['due_date'] = null;
      elem['started_at'] = null;
    }
    if (task.type == "milestone") {
      elem['is_milestone'] = true;
      elem['dependencies'] = task.dependencies;
      elem['started_at'] = $filter('date')(new Date(task.due_date), "yyyy-MM-dd H:mm:ss", "GMT");
    }
    if (task.type == "regular") {
      elem['dependencies'] = task.dependencies;
      elem['usersAdd'] = task.users;
    }

    if ($scope.tagToAdd) {
      var newTags = [];
      angular.forEach($scope.tagToAdd, function(value, key) {
        if (!value.id) {
          var data = {"data": {"projectId": $scope.projectID, "name": value.name, "color": "#000"}}; //TODO add color
          $http.post($rootScope.api.url + "/tasks/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
            .then(function successCallback(response) {
                //tag.id = (response.data.data.id);
                this.push(response.data.data.id);
            },
            function errorCallback(response) {
                notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
            });
        } else {
          this.push(value.id);
        }
      }, newTags);
      if (newTags.length)
        elem['tagsAdd'] = newTags;
    }

    var data = {"data": elem};

    $http.post($rootScope.api.url + "/task", data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.taskID = $scope.data.task.id;
        $scope.data.message = '_valid';
        memorizeTags();
        //assignUsers(task);
        notificationFactory.success("Task posted");
        $location.path("/tasks/" + $scope.projectID + "/" + $scope.taskID);
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to post task. Please try again.");
      }, $scope);
  };

  $scope.editTask = function(task) {

    var elem = {"id": $scope.taskID,
                "title": $scope.data.edit.title,
                "description": $scope.data.edit.description,
                "is_milestone": false,
                "is_container": false,
                "advance": $scope.data.edit.advance,
                };

    if ($scope.data.edit.type == "container") {
      elem['is_container'] = true;
      elem["tasksAdd"] = $scope.data.taskToAdd;
      elem["tasksRemove"] = $scope.data.taskToRemove;
    }
    if ($scope.data.edit.type == "milestone") {
      if ($scope.data.edit.due_date.getTime() != (new Date($scope.data.task.due_date).getTime())) {
        elem['due_date'] = $filter('date')(new Date($scope.data.edit.due_date), "yyyy-MM-dd H:mm:ss", "GMT"); // add 1h
        elem['started_at'] = $filter('date')(new Date($scope.data.edit.due_date), "yyyy-MM-dd H:mm:ss", "GMT");
      }
      elem['is_milestone'] = true;
    }
    if ($scope.data.edit.type == "regular") {
      if ($scope.data.edit.due_date.getTime() != (new Date($scope.data.task.due_date).getTime()))
        elem['due_date'] = $filter('date')(new Date($scope.data.edit.due_date), "yyyy-MM-dd H:mm:ss", "GMT");
      if ($scope.data.edit.started_at.getTime() != (new Date($scope.data.task.started_at).getTime()))
        elem['started_at'] = $filter('date')(new Date($scope.data.edit.started_at), "yyyy-MM-dd H:mm:ss", "GMT");
    }
    if ($scope.data.edit.advance == 100)
      elem['finished_at'] = $filter('date')(new Date(), "yyyy-MM-dd H:mm:ss", "GMT");
    else if ($scope.data.edit.advance < 100 && $scope.data.task.finished_at)
      elem['finished_at'] = null;

    if ($scope.tagToAdd) {
      var newTags = [];
      angular.forEach($scope.tagToAdd, function(value, key) {
        if (!value.id) {
          var data = {"data": {"projectId": $scope.projectID, "name": value.name, "color": "#000"}}; //TODO add color
          $http.post($rootScope.api.url + "/tasks/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
            .then(function successCallback(response) {
                //tag.id = (response.data.data.id);
                newTags.push(response.data.data.id);
            },
            function errorCallback(response) {
                notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
            });
        } else {
          this.push(value.id);
        }
      }, newTags);
      if (newTags.length)
        elem['tagsAdd'] = newTags;
    }

    if ($scope.tagToRemove) {
      var oldTags = [];
      angular.forEach($scope.tagToRemove, function(value, key) {
        this.push(value.id);
      }, oldTags);
      if (oldTags.length)
        elem['tagsRemove'] = oldTags;
    }

    if ($scope.data.edit.newDep.length)
      elem['dependencies'] = $scope.data.edit.newDep;
    if ($scope.data.edit.updateDep.length)
      elem['dependenciesUpdate'] = $scope.data.edit.updateDep;
    if ($scope.data.edit.oldDep.length)
      elem['dependenciesRemove'] = $scope.data.edit.oldDep;

    if ($scope.data.edit.newRes.length)
      elem['usersAdd'] = $scope.data.edit.newRes;
    if ($scope.data.edit.updateRes.length)
      elem['usersUpdate'] = $scope.data.edit.updateRes;
    if ($scope.data.edit.oldRes.length)
      elem['usersRemove'] = $scope.data.edit.oldRes;

    var data = {"data": elem};

    $http.put($rootScope.api.url + "/task/" + $scope.taskID, data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        memorizeTags();
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
        formatTasks();
        formatUsers();
        formatDependencies();
        notificationFactory.success("Task saved");
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to save task. Please try again.");
      });
      $scope.data.editMode["task"] = false;
  };

  $scope.finishTask = function() {
    var data = {"data": {"id": $scope.taskID, "advance": 100, "finished_at": $filter('date')(new Date(), "yyyy-MM-dd H:mm:ss", "GMT")}};

    $http.put($rootScope.api.url + "/task/" + $scope.taskID, data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        memorizeTags();
        $scope.data.task = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        notificationFactory.success("Task saved");
      },
      function errorCallback(response) {
        $scope.data.task = null;
        $scope.data.onLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "12.10.3":
            $rootScope.reject();
            break;

            case "12.10.9":
            $scope.data.message = "_denied";
            break;

            default:
            $scope.data.message = "_invalid";
            break;
          }
        notificationFactory.warning("Unable to save task. Please try again.");
      });
  };

  $scope.deleteTask = function() {
    $http.delete($rootScope.api.url + "/task/" + $scope.taskID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
          notificationFactory.success("Task deleted");
          $route.reload();
      },
      function errorCallback(resposne) {
          notificationFactory.warning("Unable to delete task. Please try again.");
      });
  };

}]);