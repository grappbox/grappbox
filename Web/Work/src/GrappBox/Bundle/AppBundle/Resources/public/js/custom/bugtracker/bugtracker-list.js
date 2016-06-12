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
app.controller('bugtrackerListController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', '$location', function($rootScope, $scope, $routeParams, $http, $cookies, Notification, $location) {

  var content = "";

  // Scope variables initialization
  $scope.projectId = 1;
  $scope.data = { onLoad: true, tickets: { }, message: "_invalid" };

  var getOpenTicketsContent = function() {
    // Get all open tickets of the project
    $http.get($rootScope.apiBaseURL + '/bugtracker/gettickets/' + $cookies.get('USERTOKEN') + '/' + $scope.projectId)
      .then(function projectsReceived(response) {
        $scope.data.tickets = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
      },
      function projectsNotReceived(response) {
        $scope.data.tickets = null;
        $scope.data.onLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "4.9.3":
            $rootScope.onUserTokenError();
            break;

            case "4.9.9":
            $scope.data.message = "_denied";
            break;

            default:
            $scope.data.message = "_invalid";
            break;
          }

      });
  };
  getOpenTicketsContent();

  var getClosedTicketsContent = function() {
    // Get all closed tickets of the project
    $http.get($rootScope.apiBaseURL + '/bugtracker/getclosedtickets/' + $cookies.get('USERTOKEN') + '/' + $scope.projectId)
      .then(function projectsReceived(response) {
        $scope.data.tickets = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
      },
      function projectsNotReceived(response) {
        $scope.data.tickets = null;
        $scope.data.onLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "4.22.3":
            $rootScope.onUserTokenError();
            break;

            case "4.22.9":
            $scope.data.message = "_denied";
            break;

            default:
            $scope.data.message = "_invalid";
            break;
          }

      });
  };

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

  // Users in string format
  $scope.formatUsersinString = function(users) {
    var usersInString = "";

    for(var i = 0; i < users.length; ++i) {
      usersInString += (i != 0 ? ", " : "") + users[i].name;
    }
    if (users.length <= 0)
      usersInString = "N/A";
    return usersInString;
  };

  $scope.getOpenTickets = function() {
    getOpenTicketsContent();
  };

  $scope.getClosedTickets = function() {
    getClosedTicketsContent();
  };

  $scope.openTicket = function(project, ticket){
    $location.path('/bugtracker/' + project + '/' + ticket);
  }

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
