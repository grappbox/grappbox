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
app.controller('bugtrackerListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, Notification) {
  // Get all projects where the user as bugtracker authorization
  var context = {"scope": $scope, "rootScope": $rootScope, "cookies": $cookies};
  $http.get($rootScope.apiBaseURL + '/dashboard/getprojectlist/' + $cookies.get('USERTOKEN'))
    .then(function successCallback(response) {
      $scope.projectListContent = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      $scope.bugtrackerProjects_isValid = true;



      if (!Object.keys($scope.projectListContent).length)
        Notification.warning({ message: 'You\'re not linked to any projects, or you might not have the rights to see their bugtracker. Please try again.', delay: null });

      $scope.bugtrackerListContent = {};
      $scope.bugtracker_isValid = {};
      $scope.bugtracker_noRights = {};
      angular.forEach($scope.projectListContent, function(project){
        context.scope.bugtrackerListContent = {};
        context.scope.bugtracker_isValid = {};
        $http.get(context.rootScope.apiBaseURL + '/bugtracker/gettickets/' + context.cookies.get('USERTOKEN') + '/' + project.project_id)
          .then(function successCallback(response) {
            context.scope.bugtrackerListContent[project.name] = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
            context.scope.bugtracker_isValid[project.name] = true;
          },
          function errorCallback(response) {
            if (response.data.info.return_code == "4.9.9")
              context.scope.bugtracker_noRights[project.name] = true;
            context.scope.bugtrackerListContent[project.name] = null;
            context.scope.bugtracker_isValid[project.name] = false;
          }
        );

      }, context);

    },
    function errorCallback(response) {
      $scope.projectListContent = null;
      $scope.bugtrackerProjects_isValid = false;
    }
  );
}]);


/**
* Routine definition
* Check if requested bugtracker is accessible
*
*/
var bugtracker_isAccessible = function($rootScope, $http, $cookies, $route, $q, $location) {
  var deferred = $q.defer();

  $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function successCallback(response) {
      deferred.resolve(true);
    },
    function errorCallback(response) {
      deferred.reject();
      $location.path('bugtracker').search({
        'id': $route.current.params.id
      });
    });

    return deferred.promise;
};

bugtracker_isAccessible['$inject'] = ['$rootScope', '$http', '$cookies', '$route', '$q', '$location'];
