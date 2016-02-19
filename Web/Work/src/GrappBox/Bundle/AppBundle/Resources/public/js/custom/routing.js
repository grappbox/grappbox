/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP routing definition
*
*/
app.config(["$routeProvider", "$locationProvider", function($routeProvider, $locationProvider) {

	$routeProvider
	// Homepage
	.when("/", {
		title: "Dashboard",
		templateUrl : "../resources/pages/dashboard.html",
		controller  : "dashboardController",
		caseInsensitiveMatch : true
	})
	.when("/login", {
		caseInsensitiveMatch : true,
		resolve: { factory: login_onSuccessRedirect }
	})
	.when("/logout", {
		caseInsensitiveMatch : true,
		resolve: { factory: logout_onSuccessRedirect }
	})
	// Bugtracker-related pages
	.when("/bugtracker", {
		title: "Bugtracker list",
		templateUrl : "../resources/pages/bugtracker-list.html",
		controller  : "bugtrackerListController",
		caseInsensitiveMatch : true
	})
	.when("/bugtracker/:projectId/:id", {
		title: "Bugtracker",
		templateUrl : "../resources/pages/bugtracker.html",
		controller  : "bugtrackerController",
		caseInsensitiveMatch : true,
		resolve: { factory: isBugtrackerAccessible }
	})
	// Calendar-related pages
	.when("/calendar", {
		title: "Calendar",
		templateUrl : "../resources/pages/calendar.html",
		controller  : "calendarController",
		caseInsensitiveMatch : true
	})
	// Cloud-related pages
	.when("/cloud", {
		title: "Cloud list",
		templateUrl : "../resources/pages/cloud-list.html",
		controller  : "cloudListController",
		caseInsensitiveMatch : true
	})
	.when("/cloud/:id", {
		title: "Cloud",
		templateUrl : "../resources/pages/cloud.html",
		controller  : "cloudController",
		caseInsensitiveMatch : true,
		resolve: { factory: isCloudAccessible }
	})
	// Notifications-related pages
	.when("/notifications", {
		title: "Notifications",
		templateUrl : "../resources/pages/notifications.html",
		controller  : "notificationsController",
		caseInsensitiveMatch : true
	})
	// User-related pages
	.when("/profile", {
		title: "Profile",
		templateUrl : "../resources/pages/profile.html",
		controller  : "profileController",
		caseInsensitiveMatch : true
	})
	// Project-related pages
	.when("/project", {
		title: "Project list",
		templateUrl : "../resources/pages/project-list.html",
		controller  : "projectListController",
		caseInsensitiveMatch : true
	})
	.when("/project/:id", {
		title: "Project",
		templateUrl : "../resources/pages/project.html",
		controller  : "projectController",
		caseInsensitiveMatch : true,
		resolve: { factory: isProjectAccessible }
	})
	// Timeline-related pages
	.when("/timeline", {
		title: "Timeline list",
		templateUrl : "../resources/pages/timeline-list.html",
		controller  : "timelineListController",
		caseInsensitiveMatch : true
	})
	.when("/timeline/:id", {
		title: "Timeline",
		templateUrl : "../resources/pages/timeline.html",
		controller  : "timelineController",
		caseInsensitiveMatch : true,
		resolve: { factory: timeline_isAccessible }
	})
	// Whiteboard-related pages
	.when("/whiteboard", {
		title: "Whiteboard list",
		templateUrl : "../resources/pages/whiteboard-list.html",
		controller  : "whiteboardListController",
		caseInsensitiveMatch : true
	})
	.when("/whiteboard/:project_id/:id", {
		title: "Whiteboard",
		templateUrl : "../resources/pages/whiteboard.html",
		controller  : "whiteboardController",
		caseInsensitiveMatch : true,
		resolve: { factory: isWhiteboardAccessible }
	})
	// Error page (default behavior)
	.otherwise({ templateUrl : "../resources/pages/404.html" });

	$locationProvider.html5Mode(true);
}]);
