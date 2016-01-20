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
		title: 'Dashboard',
		templateUrl : '../resources/pages/dashboard.html',
		controller  : 'dashboardController',
		caseInsensitiveMatch : true
	})
	.when('/login', {
		caseInsensitiveMatch : true,
		resolve: {
			factory: redirectAfterLogin
		}
	})
	.when('/notifications', {
		title: 'Notifications',
		templateUrl : '../resources/pages/notifications.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.when('/cloud', {
		title: 'Cloud',
		templateUrl : '../resources/pages/cloud-list.html',
		controller  : 'cloudListController',
		caseInsensitiveMatch : true
	})
	.when('/cloud/:id', {
		title: 'Cloud',
		templateUrl : '../resources/pages/cloud.html',
		controller  : 'cloudController',
		caseInsensitiveMatch : true,
		resolve: {
			factory: isCloudAccessible
		}
	})
	.when('/whiteboard', {
		title: 'Whiteboard',
		templateUrl : '../resources/pages/whiteboard-list.html',
		controller  : 'whiteboardListController',
		caseInsensitiveMatch : true
	})
	.when('/whiteboard/:id', {
		title: 'Whiteboard',
		templateUrl : '../resources/pages/whiteboard.html',
		controller  : 'whiteboardController',
		caseInsensitiveMatch : true,
		resolve: {
			factory: isWhiteboardAccessible
		}
	})
	.when('/profile', {
		title: 'Profile',
		templateUrl : '../resources/pages/profile.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.when('/settings', {
		title: 'Settings',
		templateUrl : '../resources/pages/settings.html',
		controller  : 'grappboxController',
		caseInsensitiveMatch : true
	})
	.when('/logout', {
		caseInsensitiveMatch : true,
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
