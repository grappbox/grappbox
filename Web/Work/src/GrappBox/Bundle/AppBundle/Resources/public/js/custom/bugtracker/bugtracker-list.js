/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP bugtracker page list (one per project)
*
*/
app.controller('bugtrackerListController', ['$scope', '$routeParams, $http', function($scope, $routeParams, $http) {
  $scope.alertList = [];

  $scope.closeAlert = function(index) {
    $scope.alertList.splice(index, 1);
  };

  $scope.alertList.push({
    type: 'warning',
    message: 'This section is under construction.'
  });

  if ($routeParams.id)
    $scope.alertList.push({
      type: 'danger',
      message: 'Ticket #' + $routeParams.id + 'doesn\'t exist, or you might not have the rights to see it. Please try again.'
    });

  $http.get('../resources/_temp/bugtraker.json').success(function(data) {
    $scope.bugtrackerListContent = data;
  });
}]);


/**
* Routine definition
* Check if requested bugtracker is accessible
*
*/
var bugtracker_isAccessible = function($http, $route, $q, $location) {
  var bugtrackerListContent = "";
  var isAccessible = false;
  var deferred = $q.defer();

  $http.get('../ressources/_temp/bugtracker.json').success(function(data) {
    bugtrackerListContent = angular.fromJson(data);

    for(i = 0; i < bugtrackerListContent.length; ++i) {
      if (bugtrackerListContent[i].id == $route.current.params.id)
        isAccessible = true;
    }

    if (isAccessible)
      deferred.resolve(true);
    else {
      deferred.reject();
      $location.path('bugtracker').search({
        'id': route.current.params.id
      });
    }

    return deferred.promise;
  });
};

bugtracker_isAccessible['$inject'] = ['$http', '$route', '$q', '$location'];
