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
app.controller('cloudController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', '$location', '$window', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, $location, $window, Notification) {
  $scope.currentProjectID = $routeParams.id;

  // Cloud default root path
  $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + ',')
    .then(function successCallback(response) {
      $scope.cloudObjects = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
      $scope.cloudObjects_isValid = true;
    },
    function errorCallback(response) {
      $scope.cloudObjects = null;
      $scope.cloudObjects_isValid = false;
    });

    // Single clic handler
    $scope.cloud_selectObject = function(object) {
      console.log(object.filename);
    };

    // Double clic handler
    $scope.cloud_accessObject = function(object) {
      $http.get($rootScope.apiBaseURL + '/cloud/file/,' + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id)
        .then(function successCallback(response) {
          $window.open($rootScope.apiBaseURL + '/cloud/file/,' + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id);
          Notification.success({ message: 'Downloaded: ' + object.filename, delay: 10000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to download ' + object.filename + '. Please try again.', delay: 10000 });
      });
    };

}]);