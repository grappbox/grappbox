/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP bugtracker page
*
*/
app.controller('bugtrackerController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, Notification) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.projectId;
  $scope.projectName = $routeParams.projectName;
  $scope.ticketID = $routeParams.id;

  //Get bugtracker informations if not new
  if ($scope.ticketID != 0) {
    $http.get($rootScope.apiBaseURL + '/bugtracker/getticket/' + $cookies.get('USERTOKEN') + '/' + $scope.ticketID)
      .then(function successCallback(response) {
        $scope.bugtracker_error = false;
        $scope.bugtracker_new = false;
        $scope.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);

      },
      function errorCallback(response) {
        $scope.bugtracker_error = true;
        $scope.bugtracker_new = false;
        $scope.ticket = null;
      });
  }
  else {
    $scope.bugtracker_error = false;
    $scope.ticket = null;
    $scope.bugtracker_new = true;
  }

  // Get ticket related comments
  var getComments = function() {
    $http.get($rootScope.apiBaseURL + '/bugtracker/getcomments/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + $scope.ticketID)
      .then(function successCallback(response) {
        $scope.commentsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      },
      function errorCallback(response) {
        $scope.commentsList = null;
      });
  };
  getComments();

  // Get all tags from the project
  var getProjectTags = function() {
    $http.get($rootScope.apiBaseURL + '/bugtracker/getprojecttags/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.tagsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      },
      function errorCallback(response) {
        $scope.tagsList = null;
      });
  };
  getProjectTags();

  // Get all users from the project
  var getProjectUsers = function() {
    $http.get($rootScope.apiBaseURL + '/dashboard/getprojectpersons/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.usersList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.usersList, function(user){

          user['name'] = user.first_name + ' ' + user.last_name;

        });
      },
      function errorCallback(response) {
        $scope.usersList = null;
      });
  };
  getProjectUsers();


  // ------------------------------------------------------
  //                 TAGS ASSIGNATION
  // ------------------------------------------------------
  $scope.tagToAdd = [];
  $scope.tagToRemove = [];

  $scope.tagAdded = function(tag) {
    //if in list of to remove
    //then remove of toremove list
    //else
    $scope.tagToAdd.push(tag);
  };

  $scope.tagRemoved = function(tag) {
    // if in list of to add
    // then remove from toadd list
    // else
    $scope.tagToRemove.push(tag);
  };

  var memorizeTags = function() {
      //in to add list
      //if object don't have id
      //create new tag
      //then attached tag
      //attach all tags from toadd list with an id
  };

  // ------------------------------------------------------
  //                 USERS ASSIGNATION
  // ------------------------------------------------------
  $scope.userToAdd = [];
  $scope.userToRemove = [];

  $scope.userAdded = function(user) {
    $scope.userToAdd.push(user);
  };

  $scope.userRemoved = function(user) {
    $scope.userToRemove.push(user);
  };

  var memorizeUsers = function() {
    var toAdd = [];
    angular.forEach($scope.userToAdd, function(user) {
      toAdd.push(user.id);
    }, toAdd);
    var toRemove = [];
    angular.forEach($scope.userToRemove, function(user) {
      toRemove.push(user.id);
    }, toRemove);

    var data = {"data": {"toAdd": toAdd, "toRemove": toRemove}};

    Notification.info({ message: 'Saving users...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/bugtracker/setparticipants/', data)
      .then(function successCallback(response) {
          Notification.success({ message: 'Users saved', delay: 5000 });
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to save users. Please try again.', delay: 5000 });
      });

  };

  // ------------------------------------------------------
  //                EDITION SWITCH
  // ------------------------------------------------------
  $scope.editMode = {};
  $scope.editMode['ticket'] = false;

  $scope.bugtracker_switchEditMode = function(elem) {
    $scope.editMode[elem] = ($scope.editMode[elem] ? false : true);
  };

  // ------------------------------------------------------
  //                    TICKET
  // ------------------------------------------------------
  $scope.bugtracker_save = function(ticket) {
    var elem = {"token": $cookies.get('USERTOKEN'),
                "bugId": ticket.id,
                "title": ticket.title,
                "description": ticket.description,
                "stateId": 1,
                "stateName": ""};
    var data = {"data": elem};

    Notification.info({ message: 'Saving ticket...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/bugtracker/editticket', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Ticket saved', delay: 5000 });
        memorizeTags();
        memorizeUsers();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to save ticket. Please try again.', delay: 5000 });
      });
      $scope.editMode['ticket'] = false;
  };

  $scope.bugtracker_post_ticket = function(new_ticket) {
    var elem = {"token": $cookies.get('USERTOKEN'),
                "projectId": $scope.projectID,
                "title": new_ticket.title,
                "description": new_ticket.description,
                "stateId": 1,
                "stateName": ""
                };
    var data = {"data": elem};

    Notification.info({ message: 'Posting ticket...', delay: 5000 });
    $http.post($rootScope.apiBaseURL + '/bugtracker/postticket', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Ticket posted', delay: 5000 });
        memorizeTags();
        memorizeUsers();
        $scope.bugtracker_error = false;
        $scope.bugtracker_new = false;
        $scope.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.ticketID = $scope.ticket.id;
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to post comment. Please try again.', delay: 5000 });
      }, $scope);
  };


  // ------------------------------------------------------
  //                    COMMENTS
  // ------------------------------------------------------
  $scope.bugtracker_edit_comment = function(comment) {
    var elem = {"token": $cookies.get('USERTOKEN'),
                "projectId": $scope.projectID,
                "commentId": comment.id,
                "title": comment.title,
                "description": comment.description
                };
    var data = {"data": elem};

    Notification.info({ message: 'Saving comment...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/bugtracker/editcomment', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Comment saved', delay: 5000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to save comment. Please try again.', delay: 5000 });
      });
      $scope.editMode[comment.id] = false;
      getComments();
  };

  $scope.bugtracker_post_comment = function(new_comment) {
    var elem = {"token": $cookies.get('USERTOKEN'),
                "projectId": $scope.projectID,
                "parentId": $scope.ticketID,
                "title": new_comment.title,
                "description": new_comment.description
                };
    var data = {"data": elem};

    Notification.info({ message: 'Posting comment...', delay: 5000 });
    $http.post($rootScope.apiBaseURL + '/bugtracker/postcomment', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Comment posted', delay: 5000 });
        new_comment.title = "";
        new_comment.description = "";
        getComments();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to post comment. Please try again.', delay: 5000 });
      }, new_comment);
  };

}]);
