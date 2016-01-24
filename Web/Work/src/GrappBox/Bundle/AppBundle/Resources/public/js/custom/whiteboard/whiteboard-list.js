/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard list page (several per project)
*
*/
app.controller('whiteboardListController', ['$scope', '$routeParams', '$http', function($scope, $routeParams, $http) {
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
      message: 'Whiteboard #' + $routeParams.id + ' doesn\'t exist, or you might not have the rights to see it. Please try again.'
    });

  $http.get('../resources/_temp/whiteboards.json').success(function(data) {
    $scope.whiteboardListContent = data;
  });
}]);


/**
* Routine definition
* Check if requested whiteboard ID is accessible
*
*/
var isWhiteboardAccessible = function($http, $route, $q, $location) {
  var whiteboardsListContent = "";
  var isAccessible = false;
  var deferred = $q.defer();

  $http.get('../resources/_temp/whiteboards.json').success(function(data) {
    whiteboardListContent = angular.fromJson(data);

    for (i = 0; i < whiteboardListContent.length; ++i) {
      if (whiteboardListContent[i].id == $route.current.params.id)
        isAccessible = true;
    }

    if (isAccessible)
      deferred.resolve(true);
    else {
      deferred.reject();
      $location.path('whiteboard').search({
        'id': $route.current.params.id
      });
    }

    return deferred.promise;
  });
};

isWhiteboardAccessible['$inject'] = ['$http', '$route', '$q', '$location'];