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
  $scope.alertList = [];

  $scope.closeAlert = function(index) {
    $scope.alertList.splice(index, 1);
  };

  if ($routeParams.id)
    $scope.alertList.push({
      type: 'danger',
      message: 'Cloud module for project #' + $routeParams.id + ' doesn\'t exist, or you might not have the rights to see it. Please try again, or create a new one.'
    });

  // Get all user's current projects (with information)
  $http.get($rootScope.apiBaseURL + '/dashboard/getprojectsglobalprogress/' + $cookies.get('USERTOKEN'))
    .then(function successCallback(response) {
      $scope.userProjectsWithCloudList = (response.data && Object.keys(response.data).length ? response.data : null);
      $scope.userProjectsWithCloudList_isValid = true;
    },
    function errorCallback(response) {
      $scope.userProjectsWithCloudList = null;
      $scope.userProjectsWithCloudList_isValid = false;
    });

}]);
