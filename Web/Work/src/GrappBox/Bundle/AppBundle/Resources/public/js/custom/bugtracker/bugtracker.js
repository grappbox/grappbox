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
app.controller('bugtrackerController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', function($rootScope, $scope, $routeParams, $http, $cookies) {

  //Scope variables initialization
  $scope.projectID = $routeParams.projectId;
  $scope.projectName = $routeParams.projectName;
  $scope.ticketID = $routeParams.id;

  // $scope.alertList = [];
  // $scope.alertList.push({
  //   type: 'warning',
  //   message: 'This section is under construction.'
  // });


  //Get bugtracker informations if not new
  if ($scope.ticketID != 0) {
    $http.get($rootScope.apiBaseURL + '/bugtracker/getticket/' + $cookies.get('USERTOKEN') + '/' + $scope.ticketID)
      .then(function successCallback(response) {
        $scope.bugtracker_error = false;
        $scope.bugtracker_new = false;
        $scope.bugtrackerContent = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);

      },
      function errorCallback(response) {
        $scope.bugtracker_error = true;
        $scope.bugtracker_new = false;
        $scope.bugtrackerContent = null;
      });
  }
  else {
    $scope.bugtracker_error = false;
    $scope.bugtrackerContent = null;
    $scope.bugtracker_new = true;
  }



  var bugtracker_edit = function() {
  };

  var bugtracker_create = function() {

  };

}]);
