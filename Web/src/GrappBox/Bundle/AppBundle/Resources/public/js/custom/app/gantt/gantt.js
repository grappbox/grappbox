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

  $scope.data = { onLoad: true, canEdit: true, tasks: [], message: "_invalid" };

  // Get all tasks of the project
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

}]);
