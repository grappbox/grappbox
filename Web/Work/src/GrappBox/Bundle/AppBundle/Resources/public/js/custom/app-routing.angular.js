/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

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
 		templateUrl : '../resources/pages/404.html'
 	})

 	$locationProvider.html5Mode(true);
 });
