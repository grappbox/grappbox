/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP Cloud list page (one per project)
*
*/
app.controller('cloudListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, Notification) {
  if ($routeParams.id)
    Notification.warning({ message: 'Project #' + $routeParams.id + ' doesn\'t exist, or you don\'t have sufficient rights. Please try again.', delay: null });

  // Get all user's current projects (with information)
  $http.get($rootScope.apiBaseURL + '/user/getprojects/' + $cookies.get('USERTOKEN'))
    .then(function successCallback(response) {
      $scope.userProjects = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
      $scope.userProjects_isValid = true;
    },
    function errorCallback(response) {
      $scope.userProjects = null;
      $scope.userProjects_isValid = false;
    });

}]);


/**
* Routine definition
* Check if requested Cloud is accessible
*
*/
var cloud_isAccessible = function($rootScope, $http, $cookies, $route, $q, $location) {
  var deferred = $q.defer();

  $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function successCallback(response) {
      deferred.resolve(true);
    },
    function errorCallback(response) {
      deferred.reject();
      $location.path('cloud').search({
        'id': $route.current.params.id
      });
    });

    return deferred.promise;
};

cloud_isAccessible['$inject'] = ['$rootScope', '$http', '$cookies', '$route', '$q', '$location'];