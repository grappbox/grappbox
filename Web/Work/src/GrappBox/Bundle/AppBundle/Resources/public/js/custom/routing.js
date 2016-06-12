/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ===================================================== */
/* ==================== PAGE ACCESS ==================== */
/* ===================================================== */

/**
* Routine definition
* APP project page access
*
*/

// Check if requested project is accessible
var isProjectAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function onGetSuccess(response) {
      deferred.resolve();
    },
    function onGetFail(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "6.3.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "6.3.4":
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "Project not found.", delay: 10000 });
          break;

          case "6.3.9":
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "You don\'t have access to this part of the project.", delay: 10000 });
          break;

          default:
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
          break;
        }
      }
      else {
        deferred.reject();
        $location.path("./");
        Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
      }
    });

    return deferred.promise;
};

// "isProjectSettingsPageAccessible" routine injection
isProjectAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];



/* ======================================================= */
/* ==================== ROUTING TABLE ==================== */
/* ======================================================= */

/**
* GRAPPBOX
* APP routing definition
*
*/
app.config(["$routeProvider", "$locationProvider", function($routeProvider, $locationProvider) {

	$routeProvider
	// Homepage
	.when("/", {
		title: "Welcome to GrappBox",
		templateUrl : "../resources/pages/dashboard-list.html",
		controller  : "dashboardListController",
		caseInsensitiveMatch : true
	})
	.when("/dashboard/:id", {
		title: "Dashboard",
		templateUrl : "../resources/pages/dashboard.html",
		controller  : "dashboardController",
		caseInsensitiveMatch : true,
		resolve: { factory: isProjectAccessible }
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
  // Task-related pages
	.when("/tasks", {
		title: "Tasks list",
		templateUrl : "../resources/pages/task-list.html",
		controller  : "taskListController",
		caseInsensitiveMatch : true
	})
	.when("/tasks/:projectId/:id", {
		title: "Task",
		templateUrl : "../resources/pages/task.html",
		controller  : "taskController",
		caseInsensitiveMatch : true,
		resolve: { factory: isTaskAccessible }
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
		resolve: { factory: isProjectAccessible }
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
	// Project settings page
	.when("/settings/:id", {
		title: "Project settings",
		templateUrl : "../resources/pages/project-settings.html",
		controller  : "projectSettingsController",
		caseInsensitiveMatch : true,
		resolve: { factory: isProjectSettingsPageAccessible }
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
		resolve: { factory: isProjectAccessible }
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
