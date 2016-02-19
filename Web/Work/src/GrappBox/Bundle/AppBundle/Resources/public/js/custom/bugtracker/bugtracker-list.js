/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP bugtracker page list (one per project)
*
*/
app.controller('bugtrackerListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, Notification) {

  var content = "";

  // Scope variables initialization
  $scope.data = { onLoad: true, projects: { }, isValid: false };

  // Get all projects where the user is associate with
  var getOpenTicketsContent = function() {
    // Get current projet bugtracker(s)
    $scope.data.projectsBugtracker_onLoad = {};
    $scope.data.projectsBugtracker_content = {};
    $scope.data.projectsBugtracker_message = {};

    // Get all tickets for each project
    context = {"scope": $scope, "rootScope": $rootScope, "cookies": $cookies};
    angular.forEach($scope.data.projects, function(project){
      context.scope.data.projectsBugtracker_onLoad[project.name] = true;

      $http.get(context.rootScope.apiBaseURL + '/bugtracker/gettickets/' + context.cookies.get('USERTOKEN') + '/' + project.project_id)
        .then(function successCallback(response) {
          context.scope.data.projectsBugtracker_onLoad[project.name] = false;
          context.scope.data.projectsBugtracker_content[project.name] = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
          context.scope.data.projectsBugtracker_message[project.name] = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        },
        function errorCallback(response) {
          context.scope.data.projectsBugtracker_onLoad[project.name] = false;
          context.scope.data.projectsBugtracker_content[project.name] = null;
          context.scope.data.projectsBugtracker_message[project.name] = "_invalid";

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "4.9.3":
              context.rootScope.onUserTokenError();
              break;

              case "4.9.9":
              context.scope.data.projectsBugtracker_message[project.name] = "_denied";
              break;

              default:
              context.scope.data.projectsBugtracker_message[project.name] = "_invalid";
              break;
            }
        });
    }, context);
  };

  var getClosedTicketsContent = function() {
    // Get current projet bugtracker(s)
    $scope.data.projectsBugtracker_onLoad = {};
    $scope.data.projectsBugtracker_content = {};
    $scope.data.projectsBugtracker_message = {};

    // Get all  closed tickets for each project
    context = {"scope": $scope, "rootScope": $rootScope, "cookies": $cookies};
    angular.forEach($scope.data.projects, function(project){
      context.scope.data.projectsBugtracker_onLoad[project.name] = true;

      $http.get(context.rootScope.apiBaseURL + '/bugtracker/getclosedtickets/' + context.cookies.get('USERTOKEN') + '/' + project.project_id)
        .then(function successCallback(response) {
          context.scope.data.projectsBugtracker_onLoad[project.name] = false;
          context.scope.data.projectsBugtracker_content[project.name] = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
          context.scope.data.projectsBugtracker_message[project.name] = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        },
        function errorCallback(response) {
          context.scope.data.projectsBugtracker_onLoad[project.name] = false;
          context.scope.data.projectsBugtracker_content[project.name] = null;
          context.scope.data.projectsBugtracker_message[project.name] = "_invalid";

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "4.22.3":
              context.rootScope.onUserTokenError();
              break;

              case "4.22.9":
              context.scope.data.projectsBugtracker_message[project.name] = "_denied";
              break;

              default:
              context.scope.data.projectsBugtracker_message[project.name] = "_invalid";
              break;
            }
        });
    }, context);
  };


  $http.get($rootScope.apiBaseURL + '/dashboard/getprojectlist/' + $cookies.get('USERTOKEN'))
    .then(function projectsReceived(response) {
      $scope.data.projects = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      $scope.data.isValid = true;
      $scope.data.onLoad = false;

      getOpenTicketsContent();
    },
    function projectsNotReceived(response) {
      $scope.data.projects = null;
      $scope.data.isValid = false;
      $scope.data.onLoad = false;
    });


  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  // Tags in string format
  $scope.formatTagsinString = function(tags) {
    var tagsInString = "";

    for(var i = 0; i < tags.length; ++i) {
      tagsInString += (i != 0 ? ", " : "") + tags[i].name;
    }
    if (tags.length <= 0)
      tagsInString = "N/A";
    return tagsInString;
  };

  $scope.getOpenTickets = function() {
    getOpenTicketsContent();
  };

  $scope.getClosedTickets = function() {
    getClosedTicketsContent();
  };

}]);


/**
* Routine definition
* APP bugtracker page access
*
*/

// Routine definition [3/3]
// Common behavior for isBugtrackerAccessible
var isBugtrackerAccessible_commonBehavior = function(deferred, $location) {
  deferred.reject();
  $location.path("bugtracker");
};

// Routine definition [2/3]
// Default behavior for isWBugtrackerAccessible
var isBugtrackerAccessible_defaultBehavior = function(deferred, $location) {
  isBugtrackerAccessible_commonBehavior(deferred, $location);
  Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
};

// Routine definition [1/3]
// Check if requested bugtracker is accessible
var isBugtrackerAccessible = function($q, $http, $rootScope, $cookies, $route, $location, Notification) {
  var deferred = $q.defer();

  if ($route.current.params.id == 0)
  {
    deferred.resolve();
    return deferred.promise;
  }

  $http.get($rootScope.apiBaseURL + '/bugtracker/getticket/' + $cookies.get('USERTOKEN') + '/' + $route.current.params.id)
    .then(function successCallback(response) {
      deferred.resolve();
    },
    function errorCallback(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "4.1.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "4.1.4":
          isBugtrackerAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "Ticket not found.", delay: 10000 });
          break;

          case "4.1.9":
          isBugtrackerAccessible_commonBehavior(deferred, $location);
          Notification.warning({ message: "You don\'t have access to this ticket.", delay: 10000 });
          break;

          default:
          isBugtrackerAccessible_defaultBehavior(deferred, $location);
          break;
        }
      }
      else { isBugtrackerAccessible_defaultBehaviorv(deferred, $location); }
    });

    return deferred.promise;
};

// "isBugtrackerAccessible" routine injection
isBugtrackerAccessible["$inject"] = ["$q", "$http", "$rootScope", "$cookies", "$route", "$location", "Notification"];
