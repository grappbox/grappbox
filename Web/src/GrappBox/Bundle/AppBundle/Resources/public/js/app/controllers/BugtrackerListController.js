/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP bugtracker list
app.controller("BugtrackerListController", ["$http", "$location", "notificationFactory", "$rootScope", "$routeParams", "$scope",
    function($http, $location, notificationFactory, $rootScope, $routeParams, $scope) {

  var content = "";

  // Scope variables initialization
  $scope.projectId = $routeParams.project_id;
  $scope.data = { onLoad: true, closedOnLoad: true, userOnLoad: true, canEdit: false,
                  tickets: { }, closedTicket: { }, userTickets: { },
                  message: "_invalid", closedMessage: '_invalid', userMessage: '_invalid' };

  var getOpenTicketsContent = function() {
    // Get all open tickets of the project
    $http.get($rootScope.api.url + '/bugtracker/tickets/opened/' + $scope.projectId, {headers: { 'Authorization': $rootScope.user.token }})
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
            $rootScope.reject();
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
    $http.get($rootScope.api.url + '/bugtracker/tickets/closed/' + $scope.projectId, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function projectsReceived(response) {
        $scope.data.closedTickets = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.closedMessage = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.closedOnLoad = false;
      },
      function projectsNotReceived(response) {
        $scope.data.closedTickets = null;
        $scope.data.closedOnLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "4.22.3":
            $rootScope.reject();
            break;

            case "4.22.9":
            $scope.data.closedMessage = "_denied";
            break;

            default:
            $scope.data.closedMessage = "_invalid";
            break;
          }

      });
  };
  getClosedTicketsContent();

  var getUserTicketsContent = function() {
    // Get all user tickets of the project
    $http.get($rootScope.api.url + '/bugtracker/tickets/user/' + $scope.projectId + '/' + $rootScope.user.id, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function projectsReceived(response) {
        $scope.data.userTickets = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.userMessage = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.userOnLoad = false;
      },
      function projectsNotReceived(response) {
        $scope.data.userTickets = null;
        $scope.data.userOnLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "4.22.3":
            $rootScope.reject();
            break;

            case "4.22.9":
            $scope.data.userMessage = "_denied";
            break;

            default:
            $scope.data.userMessage = "_invalid";
            break;
          }

      });
  };
  getUserTicketsContent();


  var getEditionRights = function() {

    $http.get($rootScope.api.url + "/role/user/part/" + $rootScope.user.id + "/" + $scope.projectId + "/bugtracker", {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.canEdit = (response.data && response.data.data && Object.keys(response.data.data).length && response.data.data.value && response.data.data.value > 1 ? true : false);
      },
      function errorCallback(response) {
        $scope.data.canEdit = false;
      });
  }
  getEditionRights();

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "-");
  };

  // Tags in string format
  $scope.formatTagsinString = function(tags) {
    var tagsInString = "";

    for(var i = 0; i < tags.length; ++i) {
      tagsInString += (i != 0 ? ", " : "") + tags[i].name;
    }
    if (tags.length <= 0)
      tagsInString = "-";
    return tagsInString;
  };

  // Users in string format
  $scope.formatUsersinString = function(users) {
    var usersInString = "";

    for(var i = 0; i < users.length; ++i) {
      usersInString += (i != 0 ? ", " : "") + users[i].firstname + " " + users[i].lastname;
    }
    if (users.length <= 0)
      usersInString = "-";
    return usersInString;
  };

  $scope.getOpenTickets = function() {
    $("#closed-tickets")[0].classList.remove("active");
    $("#closed-tickets-list")[0].classList.remove("active");
    $("#your-tickets")[0].classList.remove("active");
    $("#your-tickets-list")[0].classList.remove("active");

    $("#open-tickets")[0].classList.add("active");
    $("#open-tickets-list")[0].classList.add("active");

    //$scope.data.onLoad = true;
    //getOpenTicketsContent();
    //$scope.data.onLoad = true;
  };

  $scope.getClosedTickets = function() {
    $("#open-tickets")[0].classList.remove("active");
    $("#open-tickets-list")[0].classList.remove("active");
    $("#your-tickets")[0].classList.remove("active");
    $("#your-tickets-list")[0].classList.remove("active");

    $("#closed-tickets")[0].classList.add("active");
    $("#closed-tickets-list")[0].classList.add("active");

    //$scope.data.onLoad = true;
    //getClosedTicketsContent();
    //$scope.data.onLoad = true;
  };

  $scope.getUserTickets = function() {
    $("#open-tickets")[0].classList.remove("active");
    $("#open-tickets-list")[0].classList.remove("active");
    $("#closed-tickets")[0].classList.remove("active");
    $("#closed-tickets-list")[0].classList.remove("active");

    $("#your-tickets")[0].classList.add("active");
    $("#your-tickets-list")[0].classList.add("active");

    //$scope.data.onLoad = true;
    //getUserTicketsContent();
    //$scope.data.onLoad = false;
  };

  $scope.openTicket = function(project, ticket){
    $location.path('/bugtracker/' + project + '/' + ticket);
  }

}]);
