/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP gantt page (one per project)
*
*/

app.controller("ganttController", ["$rootScope", "$scope", "$routeParams", "$http", "Notification", "$route", "$location", 'ganttUtils', 'ganttMouseOffset', 'moment', "$uibModal",
function($rootScope, $scope, $routeParams, $http, Notification, $route, $location, utils, mouseOffset, moment, $uibModal) {


  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.projectName = $routeParams.projectName;

  $scope.data = { onLoad: true, canEdit: true, tasks: [], message: "_invalid", gantt: [] };

  // Get all tasks of the project
  var getTask = function() {
    $http.get($rootScope.api.url + "/tasks/getprojecttasks/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function projectsReceived(response) {
        $scope.data.tasks = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.12.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
        formatDataForGantt();
      },
      function projectsNotReceived(response) {
        $scope.data.tasks = null;
        $scope.data.onLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "12.14.3":
            $rootScope.onUserTokenError();
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


  // ------------------------------------------------------
  //              DATA FORMATING FOR GANTT
  // ------------------------------------------------------

  var formatDataForGantt = function() {
    //$scope.data.gantt = [];
    var elem = {};
    var dep = {};
    angular.forEach($scope.data.tasks, function(value, key) {

      if (!value.is_milestone && !value.is_container) {
        console.log('-----------------------------------');
        console.log(value.id+': '+value.title);
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                progress: value.advance,
                type: "regular",
                from: new Date(value.started_at.date),
                to: new Date(value.finished_at ? value.finished_at.date : value.due_date.date),
                tasks: [
                  {id:  value.id,
                  name: value.title,
                  color: "#BDBDBD",
                  from: new Date(value.started_at.date),
                  to: new Date(value.finished_at ? value.finished_at.date : value.due_date.date),
                  progress: value.advance,
                  }
                ]
              };
          if (value.dependencies.length)
            elem.tasks[0].dependencies = [];

        angular.forEach(value.dependencies, function(value2, key2) {
          switch (value2.name) {
            case 'fs':
              dep = {from: value2.id,
                     connectParameters: { }
                    };
              this.push(dep);
              console.log("fs with "+value2.title);
              break;
            case 'sf':
              dep = {to: value2.id,
                     type: "sf",
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              console.log("sf with "+value2.title);
              break;
            case 'ss':
              dep = {from: value2.id,
                     type: "ss",
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              console.log("ss with "+value2.title);
              break;
            case 'ff':
              dep = {from: value2.id,
                     type: "ff",
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              console.log("ff with "+value2.title);
              break;
            default:
              dep = {from: value2.id,
                     connectParameters: { } // Parameters given to jsPlumb.connect() function call.
                    };
              this.push(dep);
              break;
          }
        }, elem.tasks[0].dependencies);

      }
      else if (value.is_milestone) {
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                type: "milestone",
                from: new Date(value.due_date.date),
                to: new Date(value.due_date.date),
                tasks: [
                  {id: value.id,
                  name: value.title,
                  color: "#44BBFF",
                  from: new Date(value.due_date.date),
                  to: new Date(value.due_date.date)
                  }
                ]
              };
      }
      else if (value.is_container) {
        elem = {id: value.id,
                name: value.title,
                description: value.description,
                type: "container",
                children: []
              };
        angular.forEach(value.tasks, function (value2, key2) {
          this.push(value2.id);
        }, elem.children);
      }
      this.push(elem);


    }, $scope.data.gantt);

    // $scope.data.gantt = [
    //         {id: "10", name: 'Parent1', children: ['One', 'Two'], from: new Date('2016-08-01'), to: new Date('2016-08-21'),
    //         'rowContents': {
    //           'model.name': '{[{getValue()}]}',
    //           'from': new Date('2016-08-01'),
    //           'to': new Date('2016-08-21')
    //         }},
    //         {id: "12", name: 'One', from: new Date('2016-08-01'), to: new Date('2016-09-11'), tasks: [
    //             {name: 'task1',
    //               from: new Date('2016-08-01'),
    //               to: new Date('2016-08-21'),
    //               color: '#bdbdbd',
    //               data: 'description of task1'}
    //             ]},
    //         {id: "31", name: 'Two', tasks: [
    //             {name: 'task3',
    //               from: new Date('2016-08-01'),
    //               to: new Date('2016-08-21'),
    //               progress: {percent: "0", color:'#00B1D2', classes: []}},
    //             {name: 'task4',
    //               from: new Date('2016-09-01'),
    //               to: new Date('2016-12-21'),
    //               progress: {percent: "65", color:'#00B1D2', classes: []}}
    //           ]},
    //         {id: "13", name: 'Parent2', children: ['Three', 'Four']},
    //         {id: "50", name: 'Three', tasks: [
    //             {name: 'task1',
    //               from: new Date('2016-08-01'),
    //               to: new Date('2016-08-21'),
    //               progress: {percent: "50", color: "#FF9580", classes: []}}
    //             ]},
    //         {id: "53", name: 'Four', tasks: [
    //             {name: 'task3',
    //               from: new Date('2016-08-01'),
    //               to: new Date('2016-08-21'),
    //               progress: {percent: "25", color: "#552580", classes: []}}
    //           ]}
    //       ];

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
      columnsHeaders: {'model.name' : 'Name', 'from': 'From', 'to': 'To'},
      columnsHeaderContents: {
          'model.name': '<i class="material-icons" style="font-size:14px">storage</i> {[{getHeader()}]}',
          'from': '<i class="material-icons" style="font-size:14px">date_range</i> {[{getHeader()}]}',
          'to': '<i class="material-icons" style="font-size:14px">date_range</i> {[{getHeader()}]}'
      },
      currentDate: 'column', // ['none', 'line', 'column']
      currentDateValue: new Date(2016, 8, 2, 11, 20, 0),
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
      readOnly: true, //($scope.data.canEdit ? false : true),
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
      canDraw: function(event) {
          var isLeftMouseButton = event.button === 0 || event.button === 1;
          return $scope.options.draw && !$scope.options.readOnly && isLeftMouseButton;
      },
      drawTaskFactory: function() {
          return {
              id: utils.randomUuid(),
              name: 'Drawn task',
              color: '#AA8833'
          };
      },
      api: function(api) {
         $scope.api = api; //controll method and events from angular-gantt

         api.core.on.ready($scope, function() {

              api.tasks.on.add($scope, addEventName('tasks.on.add', logTaskEvent));
              api.tasks.on.change($scope, addEventName('tasks.on.change', logTaskEvent));
              api.tasks.on.rowChange($scope, addEventName('tasks.on.rowChange', logTaskEvent));
              api.tasks.on.remove($scope, addEventName('tasks.on.remove', logTaskEvent));

              if (api.tasks.on.moveBegin) {
                  api.tasks.on.moveBegin($scope, addEventName('tasks.on.moveBegin', logTaskEvent));
                  api.tasks.on.moveEnd($scope, addEventName('tasks.on.moveEnd', logTaskEvent));

                  api.tasks.on.resizeBegin($scope, addEventName('tasks.on.resizeBegin', logTaskEvent));
                  api.tasks.on.resizeEnd($scope, addEventName('tasks.on.resizeEnd', logTaskEvent));
              }

              api.rows.on.add($scope, addEventName('rows.on.add', logRowEvent));
              api.rows.on.change($scope, addEventName('rows.on.change', logRowEvent));
              api.rows.on.move($scope, addEventName('rows.on.move', logRowEvent));
              api.rows.on.remove($scope, addEventName('rows.on.remove', logRowEvent));

              // api.data.on.change($scope, function(newData) {
              //     console.info('[api.data.on.change] :' + newData);
              // });

              //$scope.load();

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

  $scope.expandAll = function() {
      $scope.api.tree.expandAll();
  };

  $scope.collapseAll = function() {
      $scope.api.tree.collapseAll();
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

  // "Edit task" button handler
  var onEditTask = function(row) {
    console.info(row);

    $scope.data.edit = { id: row.id, title: row.name, description: row.description, advance: row.progress, type: row.type};

    var modal_editTask = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_editTask.html", controller: "modal_editTask" });

    modal_editTask.result.then(
      function onModalConfirm() {
        var elem = {"token": $rootScope.user.token,
                    "taskId": $scope.data.edit.id,
                    "title": $scope.data.edit.title,
                    "description": $scope.data.edit.description,
                    "advance": $scope.data.edit.advance
                    };

        if ($scope.data.edit.advance == 100)
          elem['finished_at'] = new Date();
        else if ($scope.data.edit.advance < 100)
          elem['finished_at'] = null;

        var data = {"data": elem};

        $http.put($rootScope.api.url + "/tasks/taskupdate", data)
          .then(function successCallback(response) {
            $scope.reload();
          },
          function errorCallback(response) {
            Notification.warning({ message: "Unable to update task. Please try again.", delay: 5000 });
            $scope.reload();
          })
      },
      function onModalDismiss() { }
    );
  };


}]);


/**
* Controller definition (from view)
* TASK EDITION => task message form.
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
}]);
