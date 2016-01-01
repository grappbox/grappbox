/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP global routing definition
*
*/
app.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
	$routeProvider
	.when('/', {
		templateUrl : '../resources/pages/dashboard.html',
		controller  : 'dashboardController',
		caseInsensitiveMatch : true
	})
	.when('/login', {
		resolve: {
			factory: redirectAfterLogin
		}
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
	.when('/logout', {
		resolve: {
			factory: redirectAfterLogout
		}
	})
	.otherwise(
	{
		templateUrl : '../resources/pages/404.html'
	})

	$locationProvider.html5Mode(true);
}]);
