/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// APP Gantt filter
app.filter('range', function() {
  return function(input, total) {
      total = parseInt(total);

      for (var i=0; i<total; i++) {
        input.push(i);
      }

      return input;
    };
});

app.filter('objInArray', function() {
  return function(array, object) {

      var i = 0;
      while (i < array.length && object.id != array[i].id) {
        ++i;
      }
      if (i < array.length)
        return true;
      return false;
    };
});

app.filter('dependencies', function() {
  return function(input, ref) {
    var list = [];

      for (var i = 0; i < input.length; i++) {
        if (!input[i].is_container && !input[i].is_milestone && ref.id != input[i].id) {
          input[i].name = input[i].title;
          list.push(input[i]);
        }
      };
      // angular.forEach(input, function(value, key) {
      //   if (!value.is_container && !value.is_milestone && ref.id != value.id) {
      //     value.name = value.title;
      //     list.push(value);
      //   }
      // });

      return list;
    };
});


// Controller definition
// APP Gantt
app.controller("GanttController", ["$filter", "$http", "ganttUtils", "ganttMouseOffset", "$location", "moment", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope", "$uibModal",
function($filter, $http, utils, mouseOffset, $location, moment, notificationFactory, $rootScope, $route, $routeParams, $scope, $uibModal) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.projectName = $routeParams.projectName;
  $scope.labelsEnabledOpt = [{ name: 'Hidden', value: false }, { name: 'Visible', value: true }];
  $scope.viewScaleOpt = [{ name: 'Project overview', value: false }, { name: 'Precise view', value: true }];

  $scope.data = { onLoad: true, canEdit: false, tasks: [], message: "_invalid", gantt: [] };

  // Get all tasks of the project
  var getTask = function() {
    $http.get($rootScope.api.url + "/tasks/project/" + $scope.projectID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function projectsReceived(response) {
        $scope.data.tasks = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
        //$scope.data.onLoad = false;
        formatDataForGantt();
        $scope.data.onLoad = false;
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
  };
  getTask();

  var getEditionRights = function() {

    $http.get($rootScope.api.url + "/role/user/part/" + $scope.user.id + "/" + $scope.projectID + "/gantt", {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.canEdit = (response.data && response.data.data && Object.keys(response.data.data).length && response.data.data.value && response.data.data.value > 1 ? true : false);
      },
      function errorCallback(response) {
        $scope.data.canEdit = false;
      });
  }
  getEditionRights();


  // ------------------------------------------------------
  //              DATA FORMATING FOR GANTT
  // ------------------------------------------------------

  var formatDataForGantt = function() {
    //$scope.data.gantt = [];
    var elem = {};
    var dep = {};
    // $scope.firstDate = new Date();
    // $scope.lastDate = new Date();
    angular.forEach($scope.data.tasks, function(value, key) {
      // var first = new Date(value.started_at);
      // var last = new Date(value.due_date);
      // if ($scope.firstDate > first)
      //   $scope.firstDate =

      if (value.is_milestone) {
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                dependencies: value.dependencies,
                type: "milestone",
                from: new Date(value.due_date),
                to: new Date(value.due_date),
                tasks: [
                  {id: value.id,
                  name: value.title,
                  color: "#44BBFF",
                  from: new Date(value.due_date),
                  to: new Date(value.due_date),
                  type: "milestone"
                  }
                ]
              };

        if (value.dependencies.length)
          elem.tasks[0].dependencies = [];

        angular.forEach(value.dependencies, function(value2, key2) {
          switch (value2.name) {
            case 'fs':
              dep = {from: value2.task.id
                    };
              this.push(dep);
              break;
            case 'sf':
              dep = {to: value2.task.id,
                     type: "sf"
                    };
              this.push(dep);
              break;
            case 'ss':
              dep = {from: value2.task.id,
                     type: "ss"
                    };
              this.push(dep);
              break;
            case 'ff':
              dep = {from: value2.task.id,
                     type: "ff"
                    };
              this.push(dep);
              break;
            default:
              dep = {from: value2.task.id,
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              break;
          }
        }, elem.tasks[0].dependencies);
      }
      else if (value.is_container) {
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                type: "container",
                elements: value.tasks,
                children: []
              };
        angular.forEach(value.tasks, function (value2, key2) {
          this.push(value2.id);
        }, elem.children);
      }
      else if (!value.is_milestone && !value.is_container) {
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                progress: value.advance,
                users: value.users,
                dependencies: value.dependencies,
                type: "regular",
                from: new Date(value.started_at),
                to: new Date(value.finished_at ? value.finished_at : value.due_date),
                tasks: [
                  {id:  value.id,
                  name: value.title,
                  color: "#BDBDBD",
                  from: new Date(value.started_at),
                  to: new Date(value.finished_at ? value.finished_at : value.due_date),
                  progress: value.advance,
                  type: "regular"
                  }
                ]
              };

        if (value.dependencies.length)
          elem.tasks[0].dependencies = [];

        angular.forEach(value.dependencies, function(value2, key2) {
          switch (value2.name) {
            case 'fs':
              dep = {from: value2.task.id
                    };
              this.push(dep);
              break;
            case 'sf':
              dep = {to: value2.task.id,
                     type: "sf"
                    };
              this.push(dep);
              break;
            case 'ss':
              dep = {from: value2.task.id,
                     type: "ss"
                    };
              this.push(dep);
              break;
            case 'ff':
              dep = {from: value2.task.id,
                     type: "ff"
                    };
              this.push(dep);
              break;
            default:
              dep = {from: value2.task.id,
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              break;
          }
        }, elem.tasks[0].dependencies);

      }

      this.push(elem);

    }, $scope.data.gantt);

  };

  // ------------------------------------------------------
  //              GANTT MANAGEMENT
  // ------------------------------------------------------

  // Events
  var logTaskEvent = function(eventName, task) {
      console.info('[Event] ' + eventName + ': ' + task.model.name);
  };

  var logRowEvent = function(eventName, row) {
      console.info('[Event] ' + eventName + ': ' + row.model.name);
  };

  var updateTask = function(eventName, task) {

    if (task.model.type == 'milestone') {
      var elem = {"id": task.model.id,
                  "started_at": moment(task.model.to).format("YYYY-MM-DD 00:00:00"),
                  "due_date": moment(task.model.to).format("YYYY-MM-DD 00:00:01")
                  };
      task.model.from = task.model.to;
    } else {
      var elem = {"id": task.model.id,
                  "started_at": moment(task.model.from).format("YYYY-MM-DD HH:mm:ss"),
                  "due_date": moment(task.model.to).format("YYYY-MM-DD HH:mm:ss")
                  };
    }


    var data = {"data": elem};
    //console.log(data);

    $http.put($rootScope.api.url + "/task/" + task.model.id, data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        //$scope.reload();
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to update task. Please try again.");
        $scope.reload();
      })
  }

  // Event utility function
  var addEventName = function(eventName, func) {
      return function(data) {
          return func(eventName, data);
      };
  };

  // Options
  $scope.options = {
      allowSideResizing: true,
      autoExpand: 'none',
      columnMagnet: '1 day',
      //columns: ['model.name', 'from', 'to'],
      columnsClasses: {'model.name' : 'gantt-column-name', 'from': 'gantt-column-from', 'to': 'gantt-column-to'},
      columnsFormatters: {
          'from': function(from) {
              return from !== undefined ? from.format('ll') : undefined; // see moment.js format
          },
          'to': function(to) {
              return to !== undefined ? to.format('ll') : undefined;
          }
      },
      columnsHeaders: { 'model.name' : 'Name', 'from': 'From', 'to': 'To'},
      columnsHeaderContents: {
          'model.name': '<i class="material-icons" style="font-size:14px">storage</i> {[{getHeader()}]}',
          'from': '<i class="material-icons" style="font-size:14px">date_range</i> {[{getHeader()}]}',
          'to': '<i class="material-icons" style="font-size:14px">date_range</i> {[{getHeader()}]}'
      },
      containersExpansion: 'expand',
      currentDate: 'column', // ['none', 'line', 'column']
      currentDateValue: new Date(),
      daily: true,
      //data: $scope.data.gantt,
      draw: false,
      dependencies: true,
      filterTask: '',
      filterRow: '',
      fromDate: "",
      groupDisplayMode: 'group',
      labelsEnabled: true,
      maxHeight: false,
      mode: 'custom',
      readOnly: $scope.data.canEdit,
      scale: 'day',
      sideMode:'TreeTable',
      sortMode: undefined,
      taskOutOfRange: 'truncate',
      timeFramesMagnet: true,
      toDate: "",
      treeHeaderContent: '<i class="material-icons" style="font-size:14px">storage</i> {[{getHeader()}]}',
      treeTableColumns: ['from', 'to'],
      width: false,
      zoom: 1,
      api: function(api) {
         $scope.api = api; //controll method and events from angular-gantt

         api.core.on.ready($scope, function() {

              api.tasks.on.change($scope, addEventName('tasks.on.change', updateTask));
              api.tasks.on.change($scope, addEventName('tasks.on.change', logTaskEvent));

              if (api.tasks.on.moveBegin) {
                  api.tasks.on.moveBegin($scope, addEventName('tasks.on.moveBegin', logTaskEvent));
                  api.tasks.on.moveEnd($scope, addEventName('tasks.on.moveEnd', logTaskEvent));

                  api.tasks.on.resizeBegin($scope, addEventName('tasks.on.resizeBegin', logTaskEvent));
                  api.tasks.on.resizeEnd($scope, addEventName('tasks.on.resizeEnd', logTaskEvent));
              }

              // Add some DOM events
              api.directives.on.new($scope, function(directiveName, directiveScope, element) {
                  if (directiveName === 'ganttTask') {
                      element.bind('click', function(event) {
                          event.stopPropagation();
                          logTaskEvent('task-click', directiveScope.task);
                      });
                  } else if (directiveName === 'ganttRow') {
                      element.bind('click', function(event) {
                          event.stopPropagation();
                          logRowEvent('row-click', directiveScope.row);
                      });
                  } else if (directiveName === 'ganttRowLabel') {
                      element.bind('click', function() {
                          logRowEvent('row-label-click', directiveScope.row);
                          onEditTask(directiveScope.row.model);
                      });
                  }
              });
          });
      }
  };

  // Methods
  $scope.handleTaskIconClick = function(taskModel) {
      alert('Icon from ' + taskModel.name + ' task has been clicked.');
  };

  $scope.handleRowIconClick = function(rowModel) {
      alert('Icon from ' + rowModel.name + ' row has been clicked.');
  };

  $scope.containersExpansionChange = function() {
    if ($scope.options.containersExpansion == "collapse")
      $scope.api.tree.collapseAll();
    else
      $scope.api.tree.expandAll();
  };

  $scope.canAutoWidth = function(scale) {
      if (scale.match(/.*?hour.*?/) || scale.match(/.*?minute.*?/)) {
          return false;
      }
      return true;
  };

  $scope.getColumnWidth = function(widthEnabled, scale, zoom) {
      if (!widthEnabled && $scope.canAutoWidth(scale)) {
          return undefined;
      }

      if (scale.match(/.*?week.*?/)) {
          return 150 * zoom;
      }

      if (scale.match(/.*?month.*?/)) {
          return 300 * zoom;
      }

      if (scale.match(/.*?quarter.*?/)) {
          return 500 * zoom;
      }

      if (scale.match(/.*?year.*?/)) {
          return 800 * zoom;
      }

      return 40 * zoom;
  };

  $scope.load = function() {
    $scope.data.gantt = [];
    getTask();
  }

  $scope.reload = function() {
    $scope.load();
  }


  // ------------------------------------------------------
  //              MODAL MANAGEMENT
  // ------------------------------------------------------

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
        // $scope.tasksList = $filter('dependencies')($scope.tasksList, $scope.data.task);
      },
      function errorCallback(response) {
        $scope.tasksList = [];
      });
  };
  getProjectTasks();

  // "Edit task" button handler
  var onEditTask = function(row) {
    // console.info(row);

    angular.forEach(row.dependencies, function(value, key) {
      value.old = false;
      value.oldName = value.name;
    });
    angular.forEach(row.users, function(value, key) {
      value.old = false;
      value.oldPercent = value.percent;
    });
    angular.forEach(row.elements, function(value, key) {
      value.name = value.title;
    });

    // SET MODAL DATA
    $scope.data.edit = {id: row.id,
                        title: row.name,
                        description: row.description,
                        advance: row.progress,
                        type: row.type,
                        dependencies: row.dependencies,
                        users: row.users,
                        tasks: row.elements,
                        newDep: [],
                        oldDep: [],
                        updateDep: [],
                        newRes: [],
                        oldRes: [],
                        updateRes: [],
                        taskToAdd: [],
                        taskToRemove: []
                      };

    // FORMAT TASKS FOR DEPENDENCIES MANIPULATION
    $scope.tasksList = $scope.data.tasks;
    $scope.tasksList = $filter('dependencies')($scope.tasksList, row);

    angular.forEach($scope.tasksList, function(task){
      task["name"] = task.title;
    });

    // MODAL EDITION METHODS
    //---------------------TASKS ASSIGNATION-----------------------//

    $scope.taskAdded = function(task) {
      var index = -1;
      for (var i = 0; i < $scope.data.edit.taskToRemove.length && index < 0; i++) {
        if ($scope.data.edit.taskToRemove[i] == task.id || $scope.data.edit.taskToRemove[i] == task.id)
          index = i;
      }
      if (index >= 0)
        $scope.data.edit.taskToRemove.splice(index, 1);
      else
        $scope.data.edit.taskToAdd.push(task.id);
    };

    $scope.taskRemoved = function(task) {
      var index = -1;
      for (var i = 0; i < $scope.data.edit.taskToAdd.length && index < 0; i++) {
        if ($scope.data.edit.taskToAdd[i] == task.id || $scope.data.edit.taskToAdd[i] == task.id)
          index = i;
      }
      if (index >= 0)
        $scope.data.edit.taskToAdd.splice(index, 1);
      else
        $scope.data.edit.taskToRemove.push(task.id);
    };

    //-----------------DEPENDENCIES ASSIGNATION--------------------//
    $scope.addDependency = function(dep, type) {
      if (dep == "")
        return ;
      else if (type == "") {
        notificationFactory.warning("You must select a type of dependency before adding the dependency.");
        return ;
      }

      $scope.data.edit.dependencies.push({"name": type, "task": {"title": dep.title}});
      $scope.data.edit.newDep.push({"id": dep.id, "name": type});
    };

    $scope.updateDependency = function(dep) {
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
      for (var i = 0; i < $scope.data.edit.dependencies.length && index < 0; i++) {
        if ($scope.data.edit.dependencies[i].task.id == dep.task.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find dependency for update.");
        return;
      }

      $scope.data.edit.updateDep.push({"id": dep.task.id, "oldName": $scope.data.edit.dependencies[index].oldName, "newName": dep.name})
    };

    $scope.removeDependency = function(dep) {

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
      for (var i = 0; i < $scope.data.edit.dependencies.length && index < 0; i++) {
        if ($scope.data.edit.dependencies[i].id == dep.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find dependency to remove.");
        return;
      }
      $('#dependency-'+index).addClass('old');
      $('#select-dependency-'+index).addClass('old');
      $scope.data.edit.dependencies[index].old = true;
      $scope.data.edit.oldDep.push(dep.task.id);
      console.info(dep);
      console.info($scope.data.edit.oldDep);
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
      for (var i = 0; i < $scope.data.edit.dependencies.length && index < 0; i++) {
        if ($scope.data.edit.dependencies[i].id == dep.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find dependency to recover.");
        return;
      }
      $('#dependency-'+index).removeClass('old');
      $('#select-dependency-'+index).removeClass('old');
      $scope.data.edit.dependencies[index].old = false;
      $scope.data.edit.dependencies[index].name = $scope.data.edit.dependencies[index].oldName;
    }

    //------------------RESOURCES ASSIGNATION---------------------//
    $scope.addResource = function(user, workcharge) {
      if (user == "")
        return ;
      else if (workcharge == "") {
        notificationFactory.warning("You must select a workcharge percent before adding the user as resource.");
        return ;
      }

      var user = JSON.parse(user);

      $scope.data.edit.users.push({id: user.id, firstname: user.firstname, lastname: user.lastname, percent: workcharge});
      $scope.data.edit.newRes.push({"id": user.id, "percent": workcharge});
      console.info('------Add res------');
      console.info($scope.data.edit.users);
      console.info($scope.data.edit.newRes);
    };

    $scope.updateResource = function(user) {

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
      for (var i = 0; i < $scope.data.edit.users.length && index < 0; i++) {
        if ($scope.data.edit.users[i].id == user.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find resource for update.");
        return;
      }

      $scope.data.edit.updateRes.push({"id": user.id, "percent": user.percent})
    };

    $scope.removeResource = function(user) {

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
      for (var i = 0; i < $scope.data.edit.users.length && index < 0; i++) {
        if ($scope.data.edit.users[i].id == user.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find resource to remove.");
        return;
      }
      $('#resource-'+index).addClass('old');
      //$('#select-resource-'+index).addClass('old');
      $scope.data.edit.users[index].old = true;
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
      for (var i = 0; i < $scope.data.edit.users.length && index < 0; i++) {
        if ($scope.data.edit.users[i].id == user.id)
          index = i;
      }
      if (index < 0) {
        notificationFactory.warning("Unable to find resource to recover.");
        return;
      }
      $('#resource-'+index).removeClass('old');
      //$('#select-resource-'+index).removeClass('old');
      $scope.data.edit.users[index].old = false;
      $scope.data.edit.users[index].percent = $scope.data.edit.users[index].oldPercent;
    }

    // MODAL ERROR GESTION
    var modal_editTask = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_editTask.html", controller: "modal_editTask" });

    // MODAL VALIDATE GESTION
    modal_editTask.result.then(
      function onModalConfirm() {
        var elem = {"id": $scope.data.edit.id,
                    "title": $scope.data.edit.title,
                    "description": $scope.data.edit.description,
                    "advance": $scope.data.edit.advance
                    };

        if ($scope.data.edit.advance == 100)
          elem['finished_at'] = new Date();
        // else if ($scope.data.edit.advance < 100)
        //   elem['finished_at'] = null;

        console.log($scope.data.edit);

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

        if ($scope.data.edit.taskToAdd)
          elem["tasksAdd"] = $scope.data.edit.taskToAdd;
        if ($scope.data.edit.taskToRemove)
          elem["tasksRemove"] = $scope.data.edit.taskToRemove;

        var data = {"data": elem};

        $http.put($rootScope.api.url + "/task/" + $scope.data.edit.id, data, {headers: { 'Authorization': $rootScope.user.token }})
          .then(function successCallback(response) {
            $scope.reload();
          },
          function errorCallback(response) {
            notificationFactory.error("Unable to update task. Please try again.");
            $scope.reload();
          })
      },
      function onModalDismiss() { }
    );
  };

}]);


/**
* Controller definition (from view)
* TASK EDITION => task edition form.
*
*/
app.controller("modal_editTask", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.error = { title: false, description: false };

  $scope.modal_confirmTaskEdition = function() {
    $scope.error.title = ($scope.data.edit.title && $scope.data.edit.title.length > 0 ? false : true);

    var hasErrors = false;
    angular.forEach($scope.error, function(value, key) {
      if (value)
        hasErrors = true;
    });
    if (!hasErrors)
      $uibModalInstance.close();
  };
  $scope.modal_cancelTaskEdition = function() { $uibModalInstance.dismiss(); };

  // TABS MANAGEMENT
  $scope.lastTabContent = "#task-content";
  $scope.lastTabTitle = "#task-title";

  $scope.displayTab = function(tabContent, tabTitle) {

    $($scope.lastTabContent)[0].classList.remove("active");
    $($scope.lastTabTitle)[0].classList.remove("active");

    $(tabContent)[0].classList.add("active");
    $(tabTitle)[0].classList.add("active");
    $scope.lastTabContent = tabContent;
    $scope.lastTabTitle = tabTitle;
  };

  // FORM DATA
  $scope.dependenciesList = [{key: "fs", name: "finish to start"},
                             {key: "sf", name: "start to finish"},
                             {key: "ff", name: "finish to finish"},
                             {key: "ss", name: "start to start"}];

}]);
