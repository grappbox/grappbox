/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP bugtracker page
*
*/
app.controller('bugtrackerController', ['$rootScope', '$scope', '$routePamras', '$http', '$cookies', function($rootScope, $scope, $routeParams, $http, $cookies) {

  //Scope variables initialization
  $scope.projectID = $routeParams.id;

  $scope.alertList = [];
  $scope.alertList.push({
    type: 'warning',
    message: 'This section is under construction.'
  });


  var bugtracker = function() {

  };
  
}]);
