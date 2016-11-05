/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ======================================================= */
/* ==================== ROUTING TABLE ==================== */
/* ======================================================= */

// APP routing definition
app.config(["$locationProvider", "$routeProvider", function($locationProvider, $routeProvider) {
	$routeProvider
  // Homepage
  .when("/", {
		title: "GrappBox",
    controller  : "DashboardListController",
		templateUrl : "../resources/pages/dashboard-list.html",
		caseInsensitiveMatch : true,
		resolve: { projectSelection: ["accessFactory", function(accessFactory) { return accessFactory.projectSelected(); }]}
	})
	// Project dashboard
  .when("/dashboard/:project_id/", {
		title: "Dashboard",
    controller  : "DashboardController",
		templateUrl : "../resources/pages/dashboard.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
	// Login
  .when("/login", {
    title: "Loading...",
		caseInsensitiveMatch : true,
    resolve: { redirection: ["accessFactory", function(accessFactory) { return accessFactory.login(); }]}
	})
	// Logout
  .when("/logout", {
		title: "Logging out...",
    controller  : "LogoutController",
    templateUrl : "../resources/pages/logout.html",
		caseInsensitiveMatch : true
	})
	// Bugtracker pages
  .when("/bugtracker/:project_id", {
		title: "Bugtracker",
    controller  : "BugtrackerListController",
		templateUrl : "../resources/pages/bugtracker-list.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
  .when("/bugtracker/:project_id/:id", {
		title: "Bugtracker",
    controller  : "BugtrackerController",
		templateUrl : "../resources/pages/bugtracker.html",
		caseInsensitiveMatch : true,
    resolve: {
      projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }],
      bugAvailability: ["accessFactory", function(accessFactory) { return accessFactory.bugAvailable(); }]
    }
	})
  // Calendar pages
  .when("/calendar", {
    title: "Calendar",
    controller    : "CalendarController",
    controllerAs  : "vm",
    templateUrl : "../resources/pages/calendar.html",
    caseInsensitiveMatch : true
  })
  // Cloud pages
  .when("/cloud/:project_id", {
    title: "Cloud",
    controller  : "CloudController",
    templateUrl : "../resources/pages/cloud.html",
    caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
  })
  // Gantt pages
    .when("/gantt/:project_id", {
		title: "Gantt",
    controller  : "GanttController",
		templateUrl : "../resources/pages/gantt.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
  // Notifications pages
  .when("/notifications", {
    title: "Notifications",
    controller  : "NotificationController",
    templateUrl : "../resources/pages/notifications.html",
    caseInsensitiveMatch : true,
  })
  // User pages  
  .when("/profile", {
    title: "Profile",
    controller  : "ProfileController",
    templateUrl : "../resources/pages/profile.html",
    caseInsensitiveMatch : true,
  })
  // Project settings page
  .when("/settings/:project_id", {
    title: "Project settings",
    controller  : "ProjectSettingsController",
    templateUrl : "../resources/pages/project-settings.html",
    caseInsensitiveMatch : true,
    resolve: { projectSettingsAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectSettingsAvailable(); }]}
  })
  // Task pages	
  .when("/tasks/:project_id", {
		title: "Tasks",
    controller  : "TaskListController",
		templateUrl : "../resources/pages/task-list.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
  .when("/tasks/:project_id/:id", {
		title: "Tasks",
    controller  : "TaskController",
		templateUrl : "../resources/pages/task.html",
		caseInsensitiveMatch : true,
    resolve: {
      projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }],
      taskAvailability: ["accessFactory", function(accessFactory) { return accessFactory.taskAvailable(); }]
    }
  })
	// Timeline pages
  .when("/timeline/:project_id", {
		title: "Timeline",
    controller  : "TimelineController",
		templateUrl : "../resources/pages/timeline.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
	// Whiteboard pages
  .when("/whiteboard/:project_id", {
		title: "Whiteboard",
    controller  : "WhiteboardListController",
		templateUrl : "../resources/pages/whiteboard-list.html",
		caseInsensitiveMatch : true,
    resolve: { projectAvailability: ["accessFactory", function(accessFactory) { return accessFactory.projectAvailable(); }]}
	})
  .when("/whiteboard/:project_id/:id", {
		title: "Whiteboard",
    controller  : "WhiteboardController",
		templateUrl : "../resources/pages/whiteboard.html",
		caseInsensitiveMatch : true,
    resolve: { whiteboardAvailability: ["accessFactory", function(accessFactory) { return accessFactory.whiteboardAvailable(); }]}
	})
	// Error page (default behavior)
	.otherwise({
		title: "Error",
		templateUrl : "../resources/pages/404.html",
	});

	$locationProvider.html5Mode(true);
}]);