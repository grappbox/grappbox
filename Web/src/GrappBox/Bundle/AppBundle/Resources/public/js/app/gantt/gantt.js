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

app.controller("ganttController", ["$rootScope", "$scope", "$routeParams", "$http", "Notification", "$route", "$location", 'ganttUtils', 'GanttObjectModel', 'ganttMouseOffset', 'ganttDebounce', 'moment', function($rootScope, $scope, $routeParams, $http, Notification, $route, $location, utils, ObjectModel, mouseOffset, debounce, moment) {


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
      console.log('-----------------------------------');
      console.log(value.id+': '+value.title);
      if (!value.is_milestone && !value.is_container) {
        console.log("regular");
        elem = {id: value.id,
                name: value.title,
                from: new Date(value.started_at.date),
                to: new Date(value.finished_at ? value.finished_at.date : value.due_date.date),
                tasks: [
                  {id:  value.id,
                  name: value.title,
                  from: new Date(value.started_at.date),
                  to: new Date(value.finished_at ? value.finished_at.date : value.due_date.date),
                  progress: value.advance
                  }
                ]
              };
          if (value.dependencies.length)
            elem.tasks[0].dependencies = [];

        angular.forEach(value.dependencies, function(value2, key2) {
          switch (value2.name) {
            case 'fs':
              dep = {to: value2.id,
                     connectParameters: {} // Parameters given to jsPlumb.connect() function call.
                   };
              this.push(dep);
              break;
            case 'sf':

              break;
            case 'ss':

              break;
            case 'ff':

              break;
            default:
              dep = {from: value2.id,
                     connectParameters: {} // Parameters given to jsPlumb.connect() function call.
                   };
              this.push(dep);
              break;
          }
        }, elem.tasks[0].dependencies);

      }
      else if (value.is_milestone) {
        console.log("milestone");
        elem = {id: value.id,
                name: value.title,
                from: new Date(value.due_date.date),
                to: new Date(value.due_date.date),
                tasks: [
                  {id: value.id,
                  name: value.title,
                  from: new Date(value.due_date.date),
                  to: new Date(value.due_date.date),
                  progress: value.advance}
                  ]
                };
      }
      else if (value.is_container) {
        console.log("container");
        elem = {id: value.id,
                name: value.title,
                from: new Date(value.started_at.date),
                to: new Date(value.due_date.date),
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

  var objectModel;
        var dataToRemove;

        // Event handler
        var logScrollEvent = function(left, date, direction) {
            if (date !== undefined) {
                console.info('[Event] api.on.scroll: ' + left + ', ' + (date === undefined ? 'undefined' : date.format()) + ', ' + direction);
            }
        };

        // Event handler
        var logDataEvent = function(eventName) {
            console.info('[Event] ' + eventName);
        };

        // Event handler
        var logTaskEvent = function(eventName, task) {
            console.info('[Event] ' + eventName + ': ' + task.model.name);
        };

        // Event handler
        var logRowEvent = function(eventName, row) {
            console.info('[Event] ' + eventName + ': ' + row.model.name);
        };

        // Event handler
        var logTimespanEvent = function(eventName, timespan) {
            console.info('[Event] ' + eventName + ': ' + timespan.model.name);
        };

        // Event handler
        var logLabelsEvent = function(eventName, width) {
            console.info('[Event] ' + eventName + ': ' + width);
        };

        // Event handler
        var logColumnsGenerateEvent = function(columns, headers) {
            console.info('[Event] ' + 'columns.on.generate' + ': ' + columns.length + ' column(s), ' + headers.length + ' header(s)');
        };

        // // Event handler
        // var logRowsFilterEvent = function(rows, filteredRows) {
        //     console.info('[Event] rows.on.filter: ' + filteredRows.length + '/' + rows.length + ' rows displayed.');
        // };
        //
        // // Event handler
        // var logTasksFilterEvent = function(tasks, filteredTasks) {
        //     console.info('[Event] tasks.on.filter: ' + filteredTasks.length + '/' + tasks.length + ' tasks displayed.');
        // };

        // Event handler
        var logReadyEvent = function() {
            console.info('[Event] core.on.ready');
        };

        // Event utility function
        var addEventName = function(eventName, func) {
            return function(data) {
                return func(eventName, data);
            };
        };

  $scope.options = {
      allowSideResizing: true,
      autoExpand: 'none',
      columnMagnet: '1 day',
      columns: ['model.name', 'from', 'to'],
      columnsClasses: {'model.name' : 'gantt-column-name', 'from': 'gantt-column-from', 'to': 'gantt-column-to'},
      columnsFormatters: {
          'from': function(from) {
              return from !== undefined ? from.format('lll') : undefined;
          },
          'to': function(to) {
              return to !== undefined ? to.format('lll') : undefined;
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
      data: $scope.data.gantt,
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
      // rowContent: '<i class="material-icons">storage</i> {[{row.model.name}]}',
      // rowContentEnabled:false,
      scale: 'day',
      sideMode:'TreeTable',
      sortMode: undefined,
      // taskContent : '<i class="material-icons">storage</i> {[{task.model.name}]}',
      // taskContentEnabled: false,
      taskOutOfRange: 'truncate',
      timeFramesMagnet: true,
      toDate: "",
      treeHeaderContent: '<i class="material-icons" style="font-size:14px">storage</i> {[{getHeader()}]}',
      treeTableColumns: ['from', 'to'],
      width: true,
      zoom: 1,
      canDraw: function(event) {
          var isLeftMouseButton = event.button === 0 || event.button === 1;
          return $scope.options.draw && !$scope.options.readOnly && isLeftMouseButton;
      },
      drawTaskFactory: function() {
          return {
              id: utils.randomUuid(),  // Unique id of the task.
              name: 'Drawn task', // Name shown on top of each task.
              color: '#AA8833' // Color of the task in HEX format (Optional).
          };
      },
      api: function(api) {
         // API Object is used to control methods and events from angular-gantt.
         $scope.api = api;

         api.core.on.ready($scope, function() {
              // Log various events to console
              api.scroll.on.scroll($scope, logScrollEvent);
              api.core.on.ready($scope, logReadyEvent);

              // api.data.on.remove($scope, addEventName('data.on.remove', logDataEvent));
              // api.data.on.load($scope, addEventName('data.on.load', logDataEvent));
              // api.data.on.clear($scope, addEventName('data.on.clear', logDataEvent));
              api.data.on.change($scope, addEventName('data.on.change', logDataEvent));

              api.tasks.on.add($scope, addEventName('tasks.on.add', logTaskEvent));
              api.tasks.on.change($scope, addEventName('tasks.on.change', logTaskEvent));
              api.tasks.on.rowChange($scope, addEventName('tasks.on.rowChange', logTaskEvent));
              api.tasks.on.remove($scope, addEventName('tasks.on.remove', logTaskEvent));

              if (api.tasks.on.moveBegin) {
                  api.tasks.on.moveBegin($scope, addEventName('tasks.on.moveBegin', logTaskEvent));
                  //api.tasks.on.move($scope, addEventName('tasks.on.move', logTaskEvent));
                  api.tasks.on.moveEnd($scope, addEventName('tasks.on.moveEnd', logTaskEvent));

                  api.tasks.on.resizeBegin($scope, addEventName('tasks.on.resizeBegin', logTaskEvent));
                  //api.tasks.on.resize($scope, addEventName('tasks.on.resize', logTaskEvent));
                  api.tasks.on.resizeEnd($scope, addEventName('tasks.on.resizeEnd', logTaskEvent));
              }

              api.rows.on.add($scope, addEventName('rows.on.add', logRowEvent));
              api.rows.on.change($scope, addEventName('rows.on.change', logRowEvent));
              api.rows.on.move($scope, addEventName('rows.on.move', logRowEvent));
              api.rows.on.remove($scope, addEventName('rows.on.remove', logRowEvent));

              api.side.on.resizeBegin($scope, addEventName('labels.on.resizeBegin', logLabelsEvent));
              api.side.on.resize($scope, addEventName('labels.on.resize', logLabelsEvent));
              api.side.on.resizeEnd($scope, addEventName('labels.on.resizeEnd', logLabelsEvent));
              //
              // api.timespans.on.add($scope, addEventName('timespans.on.add', logTimespanEvent));
              api.columns.on.generate($scope, logColumnsGenerateEvent);

              // api.rows.on.filter($scope, logRowsFilterEvent);
              // api.tasks.on.filter($scope, logTasksFilterEvent);

              api.data.on.change($scope, function(newData) {
                  // if (dataToRemove === undefined) {
                  //     dataToRemove = [
                  //         {'id': newData[2].id}, // Remove Kickoff row
                  //         {
                  //             'id': newData[0].id, 'tasks': [
                  //             {'id': newData[0].tasks[0].id},
                  //             {'id': newData[0].tasks[3].id}
                  //         ]
                  //         }, // Remove some Milestones
                  //         {
                  //             'id': newData[7].id, 'tasks': [
                  //             {'id': newData[7].tasks[0].id}
                  //         ]
                  //         } // Remove order basket from Sprint 2
                  //     ];
                  // }
                  console.info('[api.data.on.change] :' + newData);
              });

              // // When gantt is ready, load data.
              // // `data` attribute could have been used too.
              // $scope.load();

              // Add some DOM events
              api.directives.on.new($scope, function(directiveName, directiveScope, element) {
                  if (directiveName === 'ganttTask') {
                      element.bind('click', function(event) {
                          event.stopPropagation();
                          logTaskEvent('task-click', directiveScope.task);
                      });
                      element.bind('mousedown touchstart', function(event) {
                          event.stopPropagation();
                          $scope.live.row = directiveScope.task.row.model;
                          if (directiveScope.task.originalModel !== undefined) {
                              $scope.live.task = directiveScope.task.originalModel;
                          } else {
                              $scope.live.task = directiveScope.task.model;
                          }
                          $scope.$digest();
                      });
                  } else if (directiveName === 'ganttRow') {
                      element.bind('click', function(event) {
                          event.stopPropagation();
                          logRowEvent('row-click', directiveScope.row);
                      });
                      element.bind('mousedown touchstart', function(event) {
                          event.stopPropagation();
                          $scope.live.row = directiveScope.row.model;
                          $scope.$digest();
                      });
                  } else if (directiveName === 'ganttRowLabel') {
                      element.bind('click', function() {
                          logRowEvent('row-label-click', directiveScope.row);
                      });
                      element.bind('mousedown touchstart', function() {
                          $scope.live.row = directiveScope.row.model;
                          $scope.$digest();
                      });
                  }
              });

              api.tasks.on.rowChange($scope, function(task) {
                  $scope.live.row = task.row.model;
              });

              objectModel = new ObjectModel(api);
          });
      }
  };

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

  // $scope.$watch('options.sideMode', function(newValue, oldValue) {
  //     if (newValue !== oldValue) {
  //         $scope.api.side.setWidth(undefined);
  //         $timeout(function() {
  //             $scope.api.columns.refresh();
  //         });
  //     }
  // });

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

  // // Reload data action
  // $scope.load = function() {
  //     $scope.data.gantt = formatDataForGantt();
  //     dataToRemove = undefined;
  // };
  //
  $scope.reload = function() {
    $scope.data.gantt = [];
    getTask();
      //$scope.load();
  };
  //
  // // Remove data action
  // $scope.remove = function() {
  //     $scope.api.data.remove(dataToRemove);
  //     $scope.api.dependencies.refresh();
  // };
  //
  // // Clear data action
  // $scope.clear = function() {
  //     $scope.data.gantt = [];
  // };


  // Visual two way binding.
  $scope.live = {};

  var debounceValue = 1000;

  var listenTaskJson = debounce(function(taskJson) {
      if (taskJson !== undefined) {
          var task = angular.fromJson(taskJson);
          objectModel.cleanTask(task);
          var model = $scope.live.task;
          angular.extend(model, task);
      }
  }, debounceValue);
  $scope.$watch('live.taskJson', listenTaskJson);

  var listenRowJson = debounce(function(rowJson) {
      if (rowJson !== undefined) {
          var row = angular.fromJson(rowJson);
          objectModel.cleanRow(row);
          var tasks = row.tasks;

          delete row.tasks;
          var rowModel = $scope.live.row;

          angular.extend(rowModel, row);

          var newTasks = {};
          var i, l;

          if (tasks !== undefined) {
              for (i = 0, l = tasks.length; i < l; i++) {
                  objectModel.cleanTask(tasks[i]);
              }

              for (i = 0, l = tasks.length; i < l; i++) {
                  newTasks[tasks[i].id] = tasks[i];
              }

              if (rowModel.tasks === undefined) {
                  rowModel.tasks = [];
              }
              for (i = rowModel.tasks.length - 1; i >= 0; i--) {
                  var existingTask = rowModel.tasks[i];
                  var newTask = newTasks[existingTask.id];
                  if (newTask === undefined) {
                      rowModel.tasks.splice(i, 1);
                  } else {
                      objectModel.cleanTask(newTask);
                      angular.extend(existingTask, newTask);
                      delete newTasks[existingTask.id];
                  }
              }
          } else {
              delete rowModel.tasks;
          }

          angular.forEach(newTasks, function(newTask) {
              rowModel.tasks.push(newTask);
          });
      }
  }, debounceValue);
  $scope.$watch('live.rowJson', listenRowJson);

  $scope.$watchCollection('live.task', function(task) {
      $scope.live.taskJson = angular.toJson(task, true);
      $scope.live.rowJson = angular.toJson($scope.live.row, true);
  });

  $scope.$watchCollection('live.row', function(row) {
      $scope.live.rowJson = angular.toJson(row, true);
      if (row !== undefined && row.tasks !== undefined && row.tasks.indexOf($scope.live.task) < 0) {
          $scope.live.task = row.tasks[0];
      }
  });

  $scope.$watchCollection('live.row.tasks', function() {
      $scope.live.rowJson = angular.toJson($scope.live.row, true);
  });

}]);
