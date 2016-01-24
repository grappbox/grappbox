/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP timeline page list (one per project)
*
*/
app.controller('timelineListController', ['$scope', function($scope) {
  $scope.alertList = [];
  $scope.alertList.push({
    type: 'warning',
    message: 'This section is under construction.'
  });
}]);


/**
* Routine definition
* Check if requested timeline is accessible
*
*/
var timeline_isAccessible = function($q) {
  var deferred = $q.defer();
  deferred.resolve(true);

  return deferred.promise;
};

timeline_isAccessible['$inject'] = ['$q'];