/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

 /* grappbox : dashboard data */
 var app = angular.module('grappbox', ['ngRoute']).config(function($interpolateProvider) {
  $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});

 app.controller('teamOccupationDashboardController', function($scope, $http) {
  $http.get('../resources/_temp/team-occupation.json').success(function(data) {
    $scope.teamOccupationList = data;
  });
});

 app.controller('nextMeetingsDashboardController', function($scope, $http) {
  $http.get('../resources/_temp/next-meetings.json').success(function(data) {
    $scope.nextMeetingsList = data;
  });
});

 app.controller('globalProgressDashboardController', function($scope, $http) {
  $http.get('../resources/_temp/global-progress.json').success(function(data) {
    $scope.globalProgressList = data;
  });
});

 /* grappbox : global routing */
 app.config(function($routeProvider, $locationProvider) {
   $routeProvider
   .when('/dashboard', {
     templateUrl : '../resources/pages/dashboard.html',
     controller  : 'grappboxMainController'
   })
   .when('/whiteboard', {
     templateUrl : '../resources/pages/whiteboard.html',
     controller  : 'grappboxWhiteboardController'
   })

    $locationProvider.html5Mode(true);
 });

 app.controller('grappboxMainController', function($scope) {
  $scope.message = 'Everyone come and see how good I look!';
});

 app.controller('grappboxWhiteboardController', function($scope) { });