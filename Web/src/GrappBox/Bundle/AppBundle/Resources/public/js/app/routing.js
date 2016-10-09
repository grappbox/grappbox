/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ===================================================== */
/* ==================== PAGE ACCESS ==================== */
/* ===================================================== */

// Routine definition
// APP project-related page access
var isProjectAccessible = function($rootScope, $q, $http, $route, $location, Notification) {
  var deferred = $q.defer();


  $http.get($rootScope.api.url + "/project/"+ $route.current.params.project_id, {headers: {"Authorization": $rootScope.user.token}}).then(
  	function onGetProjectInformationsSuccess(response) {
      deferred.resolve();
    },
    function onGetProjectInformationsFail(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "6.3.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "6.3.4":
          deferred.reject();
          $location.path("./");
          Notification.warning({ title: "GrappBox", message: "Project not found.", delay: 4500 });
          break;

          case "6.3.9":
          deferred.reject();
          $location.path("./");
          Notification.warning({ title: "GrappBox", message: "You don\'t have access to this part of the project.", delay: 4500 });
          break;

          default:
          deferred.reject();
          $location.path("./");
          Notification.warning({ title: "GrappBox", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
          break;
        }
      }
      else {
        deferred.reject();
        $location.path("./");
        Notification.warning({ title: "GrappBox", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
      }
    });

    return deferred.promise;
};

// "isProjectSettingsPageAccessible" routine injection
isProjectAccessible["$inject"] = ["$rootScope", "$q", "$http", "$route", "$location", "Notification"];



/* ======================================================= */
/* ==================== ROUTING TABLE ==================== */
/* ======================================================= */

// GRAPPBOX
// APP routing definition
app.config(["$routeProvider", "$locationProvider", function($routeProvider, $locationProvider) {
	$routeProvider
	// Homepage
	.when("/", {
		title: "Welcome to GrappBox",
		templateUrl : "../resources/pages/dashboard-list.html",
		controller  : "dashboardListController",
		caseInsensitiveMatch : true,
		homepage: true,
		resolve: { factory: isProjectSelected }
	})
	.when("/dashboard/", {
		title: "Welcome to GrappBox",
		templateUrl : "../resources/pages/dashboard-list.html",
		controller  : "dashboardListController",
		caseInsensitiveMatch : true,
		homepage: true,
		resolve: { factory: isProjectSelected }
	})
	// Project dashboard
	.when("/dashboard/:project_id/", {
		title: "Dashboard",
		templateUrl : "../resources/pages/dashboard.html",
		controller  : "dashboardController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	// Login
	.when("/login", {
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: redirectOnLogin }
	})
	// Logout
	.when("/logout", {
		title: "Logging out...",
		caseInsensitiveMatch : true,
		homepage: true,
		resolve: { factory: redirectOnLogout }
	})
	// Bugtracker-related pages
	.when("/bugtracker/:project_id", {
		title: "Bugtracker list",
		templateUrl : "../resources/pages/bugtracker-list.html",
		controller  : "bugtrackerListController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	.when("/bugtracker/:project_id/:id", {
		title: "Bugtracker",
		templateUrl : "../resources/pages/bugtracker.html",
		controller  : "bugtrackerController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isBugtrackerAccessible }
	})
  // Gantt-related pages
  .when("/gantt/:project_id", {
		title: "Gantt",
		templateUrl : "../resources/pages/gantt.html",
		controller  : "ganttController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
  // Task-related pages
	.when("/tasks/:project_id", {
		title: "Tasks list",
		templateUrl : "../resources/pages/task-list.html",
		controller  : "taskListController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	.when("/task/:project_id/:id", {
		title: "Task",
		templateUrl : "../resources/pages/task.html",
		controller  : "taskController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isTaskAccessible }
	})
	// Calendar-related pages
	.when("/calendar", {
		title: "Calendar",
		templateUrl : "../resources/pages/calendar.html",
		controller  : null,
		caseInsensitiveMatch : true,
		homepage: false
	})
	// Cloud-related pages
	.when("/cloud/:project_id", {
		title: "Cloud",
		templateUrl : "../resources/pages/cloud.html",
		controller  : "cloudController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	// Notifications-related pages
	.when("/notifications", {
		title: "Notifications",
		templateUrl : "../resources/pages/notifications.html",
		controller  : "notificationsController",
		caseInsensitiveMatch : true,
		homepage: false
	})
	// User-related pages
	.when("/profile", {
		title: "Profile",
		templateUrl : "../resources/pages/profile.html",
		controller  : "profileController",
		caseInsensitiveMatch : true,
		homepage: false
	})
	// Project settings page
	.when("/settings/:project_id", {
		title: "Project settings",
		templateUrl : "../resources/pages/project-settings.html",
		controller  : "projectSettingsController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectSettingsPageAccessible }
	})
	// Timeline-related pages
	.when("/timeline/:project_id", {
		title: "Timeline",
		templateUrl : "../resources/pages/timeline.html",
		controller  : "timelineController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	// Whiteboard-related pages
	.when("/whiteboard/:project_id", {
		title: "Whiteboard list",
		templateUrl : "../resources/pages/whiteboard-list.html",
		controller  : "whiteboardListController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isProjectAccessible }
	})
	.when("/whiteboard/:project_id/:id", {
		title: "Whiteboard",
		templateUrl : "../resources/pages/whiteboard.html",
		controller  : "whiteboardController",
		caseInsensitiveMatch : true,
		homepage: false,
		resolve: { factory: isWhiteboardAccessible }
	})
	// Error page (default behavior)
	.otherwise({
		title: "Error",
		templateUrl : "../resources/pages/404.html",
		homepage: false
	});

	$locationProvider.html5Mode(true);
}]);
