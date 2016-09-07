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

app.controller("ganttController", ["$rootScope", "$scope", "$routeParams", "$http", "Notification", "$route", "$location", function($rootScope, $scope, $routeParams, $http, Notification, $route, $location) {

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
    $scope.data.options= {
      fromDate: "",
      toDate : ""//,
      // columns: ['model.name', 'model.from', 'model.to'],
      // formater: { 'model.name': 'Name', 'model.from': 'From', 'model.to': 'To'}
    };

    $scope.data.gantt = [
            {id: "10", name: 'Parent1', children: ['One', 'Two'], from: new Date('2016-08-01'), to: new Date('2016-08-21'),
            'rowContents': {
              'model.name': '{[{getValue()}]}',
              'from': new Date('2016-08-01'),
              'to': new Date('2016-08-21')
            }},
            {id: "12", name: 'One', form: new Date('2016-08-01'), to: new Date('2016-09-11'), tasks: [
                {name: 'task1',
                  from: new Date('2016-08-01'),
                  to: new Date('2016-08-21'),
                  color: '#bdbdbd',
                  data: 'description of task1'}
                ]},
            {id: "31", name: 'Two', tasks: [
                {name: 'task3',
                  from: new Date('2016-08-01'),
                  to: new Date('2016-08-21'),
                  progress: {percent: "0", color:'#00B1D2', classes: []}},
                {name: 'task4',
                  from: new Date('2016-09-01'),
                  to: new Date('2016-12-21'),
                  progress: {percent: "65", color:'#00B1D2', classes: []}}
              ]},
            {id: "13", name: 'Parent2', children: ['Three', 'Four']},
            {id: "50", name: 'Three', tasks: [
                {name: 'task1',
                  from: new Date('2016-08-01'),
                  to: new Date('2016-08-21'),
                  progress: {percent: "50", color: "#FF9580", classes: []}}
                ]},
            {id: "53", name: 'Four', tasks: [
                {name: 'task3',
                  from: new Date('2016-08-01'),
                  to: new Date('2016-08-21'),
                  progress: {percent: "25", color: "#552580", classes: []}}
              ]}
          ];

  };
  formatDataForGantt();

}]);
