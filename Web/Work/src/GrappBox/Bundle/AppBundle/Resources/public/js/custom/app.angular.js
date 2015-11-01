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
var app = angular.module('grappbox', ['ngRoute', 'ui.bootstrap']).config(['$interpolateProvider', function($interpolateProvider) {
	$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
}]);

app.controller('grappboxController', function() {} );


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
* Global routing
*
*/
app.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
	$routeProvider
	.when('/', {
		templateUrl : '../resources/pages/dashboard.html',
		controller  : 'dashboardController',
		caseInsensitiveMatch : true
	})
	.when('/notifications', {
		templateUrl : '../resources/pages/notifications.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.when('/whiteboard', {
		templateUrl : '../resources/pages/whiteboard-list.html',
		controller  : 'whiteboardListController',
		caseInsensitiveMatch : true
	})
	.when('/whiteboard/:id', {
		templateUrl : '../resources/pages/whiteboard.html',
		controller  : 'whiteboardController',
		caseInsensitiveMatch : true,
		resolve: {
			factory: isWhiteboardAccessible
		}
	})
	.when('/profile', {
		templateUrl : '../resources/pages/profile.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.when('/settings', {
		templateUrl : '../resources/pages/settings.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.otherwise(
	{
		templateUrl : '../resources/pages/404.html'
	})

	$locationProvider.html5Mode(true);
}]);


/**
* Dashboard data
*
*/
app.controller('dashboardController', ['$scope', '$http', function($scope, $http) {
	$http.get('../resources/_temp/team-occupation.json').success(function(data) {
		$scope.teamOccupationList = data;
	});

	$http.get('../resources/_temp/next-meetings.json').success(function(data) {
		$scope.nextMeetingsList = data;
	});

	$http.get('../resources/_temp/global-progress.json').success(function(data) {
		$scope.globalProgressList = data;
	});
}]);


/**
* Whiteboard list data
*
*/
app.controller('whiteboardListController', ['$scope', '$routeParams', '$http', function($scope, $routeParams, $http) {
	$scope.alertList = [];

	if ($routeParams.id)
		$scope.alertList.push( { type: 'danger', message: 'Whiteboard #' + $routeParams.id + ' doesn\'t exist, or you might not have the rights to see it. Please try again.' } );

	$http.get('../resources/_temp/whiteboards.json').success(function(data) {
		$scope.whiteboardListContent = data;
	});
}]);

var isWhiteboardAccessible = function($http, $route, $q, $location) {
	var whiteboardsListContent = "";
	var isAccessible = false;
	var deferred = $q.defer();

	$http.get('../resources/_temp/whiteboards.json').success(function(data) {
		whiteboardListContent = angular.fromJson(data);

		for (i = 0; i < whiteboardListContent.length; ++i)
		{
			if (whiteboardListContent[i].id == $route.current.params.id)
				isAccessible = true;
		}

		if (isAccessible)
			deferred.resolve(true);
		else
		{
			deferred.reject();
			$location.path('whiteboard').search( {'id': $route.current.params.id} );
		}

		return deferred.promise;
	});
};

isWhiteboardAccessible['$inject'] = ['$http', '$route', '$q', '$location'];


/**
* Whiteboard (single) data
*
*/
app.controller('whiteboardController', ['$scope', '$http', '$routeParams', function($scope, $http, $routeParams) {
	$scope.whiteboardID = $routeParams.id;

	$http.get('../resources/_temp/whiteboards.json').success(function(data) {
		$scope.whiteboardsListContent = data;
	});
}]);
