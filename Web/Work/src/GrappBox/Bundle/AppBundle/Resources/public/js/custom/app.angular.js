/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */


 /* grappbox : symbol overload */
 var app = angular.module('grappbox', ['ngRoute']).config(function($interpolateProvider) {
  $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});


 /* grappbox : global routing */
 app.config(function($routeProvider, $locationProvider) {
   $routeProvider
   .when('/', {
     templateUrl : '../resources/pages/dashboard.html',
     controller  : 'grappboxController'
   })
   .when('/dashboard', {
     templateUrl : '../resources/pages/dashboard.html',
     controller  : 'grappboxController'
   })
   .when('/whiteboard', {
     templateUrl : '../resources/pages/whiteboard-home.html',
     controller  : 'grappboxController'
   })
   .otherwise(
   {
      redirectTo : '/'
   })

    $locationProvider.html5Mode(true);
 });


 /* grappbox : dashboard data */
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

 app.controller('grappboxController', function($scope) { });


/* grappbox : whiteboard data */
 app.controller('grappboxWhiteboardController', function($scope) { });
