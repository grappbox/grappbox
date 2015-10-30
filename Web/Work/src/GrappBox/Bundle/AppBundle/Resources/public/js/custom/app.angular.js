/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
 */

/**
 * GRAPPBOX
 * APP and main controller definition
 * TWIG template conflict fix
 *
 */
 var app = angular.module('grappbox', ['ngRoute']).config(['$interpolateProvider', function($interpolateProvider) {
  $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.controller('grappboxController', ['$scope', function($scope) { } ]);


/**
 * Global routing
 *
 */
 app.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
 	$routeProvider
 	.when('/', {
 		templateUrl : '../resources/pages/dashboard.html',
 		controller  : 'grappboxController'
 	})
 	.when('/notifications', {
 		templateUrl : '../resources/pages/notifications.html',
 		controller  : 'grappboxController'
 	})
 	.when('/whiteboard', {
 		templateUrl : '../resources/pages/whiteboard.html',
 		controller  : 'grappboxController'
 	})
 	.when('/whiteboard/id', {
 		templateUrl : '../resources/pages/whiteboard-draw.html',
 		controller  : 'grappboxController'
 	})
 	.when('/profile', {
 		templateUrl : '../resources/pages/profile.html',
 		controller  : 'grappboxController'
 	})
 	.when('/settings', {
 		templateUrl : '../resources/pages/settings.html',
 		controller  : 'grappboxController'
 	})
 	.otherwise(
 	{
 		templateUrl : '../resources/pages/404.html'
 	})

 	$locationProvider.html5Mode(true);
 }]);


/**
 * Sidebar controller
 *
 */
app.controller('sidebarController', ['$scope', '$location', function($scope, $location) {
    $scope.isActive = function(route) {
        return route === $location.path();
    };
}]);


/**
 * Dashboard data
 *
 */
 app.controller('teamOccupationDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/team-occupation.json').success(function(data) {
 		$scope.teamOccupationList = data;
 	});
 }]);

 app.controller('nextMeetingsDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/next-meetings.json').success(function(data) {
 		$scope.nextMeetingsList = data;
 	});
 }]);

 app.controller('globalProgressDashboardController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/global-progress.json').success(function(data) {
 		$scope.globalProgressList = data;
 	});
 }]);


/**
 * Whiteboard data
 *
 */
 app.controller('allWhiteboardsController', ['$scope', '$http', function($scope, $http) {
 	$http.get('../resources/_temp/whiteboards.json').success(function(data) {
 		$scope.allWhiteboardsList = data;
 	});
 }]);