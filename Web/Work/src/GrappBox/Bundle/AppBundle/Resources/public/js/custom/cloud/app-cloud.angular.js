/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP Cloud modules list (one for each project)
*
*/
app.controller('cloudController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', function($rootScope, $scope, $routeParams, $http, $cookies) {
  $scope.view.pageTitle = 'Cloud';
  $scope.currentProjectID = $routeParams.id;

  // Get all Cloud default path
  $http.get($rootScope.apiBaseURL + '/cloud/getlist/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + ',')
    .then(function successCallback(response) {
      $scope.cloudObjects = (response.data && Object.keys(response.data).length ? response.data.data : null);
      $scope.cloudObjects_isValid = true;
    },
    function errorCallback(response) {
      $scope.cloudObjects = null;
      $scope.cloudObjects_isValid = false;
    });

}]);
