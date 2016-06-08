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
app.controller('bugtrackerController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', '$route', '$location', function($rootScope, $scope, $routeParams, $http, $cookies, Notification, $route, $location) {

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
    $scope.tags = [];
    $scope.users = [];
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
  if ($scope.ticketId != 0)
    getComments();

  // Get all tags from the project
  var getProjectTags = function() {
    $http.get($rootScope.apiBaseURL + '/bugtracker/getprojecttags/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.tagsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      },
      function errorCallback(response) {
        $scope.tagsList = [];
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
        $scope.usersList = [];
      });
  };
  getProjectUsers();


  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };


  // ------------------------------------------------------
  //                 TAGS ASSIGNATION
  // ------------------------------------------------------
  $scope.tagToAdd = [];
  $scope.tagToRemove = [];

  $scope.tagAdded = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.tagToRemove.length && index < 0; i++) {
      if ($scope.tagToRemove[i].id == tag.id)
        index = i;
    }

    if (index >= 0)
      $scope.tagToRemove.splice(index, 1);
    else
      $scope.tagToAdd.push(tag);
  };

  $scope.tagRemoved = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.tagToAdd.length && index < 0; i++) {
      if ($scope.tagToAdd[i].id == tag.id)
        index = i;
    }
    if (index >= 0)
      $scope.tagToAdd.splice(index, 1);
    else
      $scope.tagToRemove.push(tag);
  };

  var memorizeTags = function() {
    var context = {"rootScope": $rootScope, "http": $http, "Notification": Notification, "cookies": $cookies, "scope": $scope};

    angular.forEach($scope.tagToAdd, function(tag) {
      if (!tag.id) {
        var data = {"data": {"token": context.cookies.get('USERTOKEN'), "projectId": context.scope.projectID, "name": tag.name}};
        context.http.post(context.rootScope.apiBaseURL + '/bugtracker/tagcreation', data)
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);
          },
          function errorCallback(resposne) {
              Notification.warning({ message: 'Unable to create tag: ' + tag.name + '. Please try again.', delay: 5000 });
          });
      }

      var data = {"data": {"token": context.cookies.get('USERTOKEN'), "bugId": context.scope.ticketID, "tagId": tag.id}};
      context.http.put(context.rootScope.apiBaseURL + '/bugtracker/assigntag', data)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: 'Unable to assign tag: ' + tag.name + '. Please try again.', delay: 5000 });
        });
    }, context);

    angular.forEach($scope.tagToRemove, function(tag) {
      context.http.delete(context.rootScope.apiBaseURL + '/bugtracker/removetag/' + context.cookies.get('USERTOKEN') + '/' + context.scope.ticketID + '/' + tag.id)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: 'Unable to remove tag: ' + tag.name + '. Please try again.', delay: 5000 });
        });
    }, context);
  };

  // ------------------------------------------------------
  //                 USERS ASSIGNATION
  // ------------------------------------------------------
  $scope.userToAdd = [];
  $scope.userToRemove = [];

  $scope.userAdded = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.userToRemove.length && index < 0; i++) {
      if ($scope.userToRemove[i].user_id == user.user_id || $scope.userToRemove[i].id == user.user_id)
        index = i;
    }
    if (index >= 0)
      $scope.userToRemove.splice(index, 1);
    else
      $scope.userToAdd.push(user);
  };

  $scope.userRemoved = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.userToAdd.length && index < 0; i++) {
      if ($scope.userToAdd[i].user_id == user.user_id || $scope.userToAdd[i].user_id == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.userToAdd.splice(index, 1);
    else
      $scope.userToRemove.push(user);
  };

  var memorizeUsers = function() {
    var toAdd = [];
    angular.forEach($scope.userToAdd, function(user) {
      toAdd.push(user.user_id);
    }, toAdd);
    var toRemove = [];
    angular.forEach($scope.userToRemove, function(user) {
      toRemove.push(user.id);
    }, toRemove);

    var data = {"data": {"token": $cookies.get('USERTOKEN'), "bugId": $scope.ticketID, "toAdd": toAdd, "toRemove": toRemove}};

    Notification.info({ message: 'Saving users...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/bugtracker/setparticipants', data)
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
        $scope.bugtracker_error = false;
        $scope.bugtracker_new = false;
        $scope.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.ticketID = $scope.ticket.id;
        memorizeTags();
        memorizeUsers();
        Notification.success({ message: 'Ticket posted', delay: 5000 });
        $location.path('/bugtracker/' + $scope.projectID + '/' + $scope.ticketID);
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to post comment. Please try again.', delay: 5000 });
      }, $scope);
  };

  $scope.bugtracker_close_ticket = function() {
    Notification.info({ message: 'Closing ticket ...', delay: 5000 });
    $http.delete($rootScope.apiBaseURL + '/bugtracker/closeticket/' + $cookies.get('USERTOKEN') + '/' + $scope.ticketID)
      .then(function successCallback(response) {
          Notification.success({ message: 'Ticket closed', delay: 5000 });
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to close ticket. Please try again.', delay: 5000 });
      });
  };

  $scope.bugtracker_reopen_ticket = function() {
    Notification.info({ message: 'Reopening ticket ...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/bugtracker/reopenticket/' + $cookies.get('USERTOKEN') + '/' + $scope.ticketID)
      .then(function successCallback(response) {
          Notification.success({ message: 'Ticket reopened', delay: 5000 });
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to reopen ticket. Please try again.', delay: 5000 });
      });
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
        $scope.editMode[comment.id] = false;
        getComments();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to save comment. Please try again.', delay: 5000 });
        $scope.editMode[comment.id] = false;
        getComments();
      });

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

  $scope.bugtracker_delete_comment = function(comment_id) {
    $http.delete($rootScope.apiBaseURL + '/bugtracker/closeticket/' + $cookies.get('USERTOKEN') + '/' + comment_id)
      .then(function successCallback(response) {
          Notification.success({ message: 'Comment deleted', delay: 5000 });
          getComments();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to delete comment. Please try again.', delay: 5000 });
      });
  };

}]);
