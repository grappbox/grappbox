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
app.controller('bugtrackerController', ['$rootScope', '$scope', '$routeParams', '$http', 'Notification', '$route', '$location', 'timelineFactory', function($rootScope, $scope, $routeParams, $http, Notification, $route, $location, timelineFactory) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.ticketID = $routeParams.id;

  $scope.data = { onLoad: true, bugtracker_new: false, canEdit: true, ticket: { }, tags: [], users: [], message: "_invalid" };

  //Get bugtracker informations set up data
  if ($scope.ticketID != 0) {
    $http.get($rootScope.api.url + '/bugtracker/getticket/' + $rootScope.user.token + '/' + $scope.ticketID)
      .then(function successCallback(response) {
        $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
      },
      function errorCallback(response) {
        $scope.data.ticket = null;
        $scope.data.onLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "4.1.3":
            $rootScope.onUserTokenError();
            break;

            case "4.1.9":
            $scope.data.message = "_denied";
            break;

            default:
            $scope.data.message = "_invalid";
            break;
          }
      });
  }
  else {
    if (timelineFactory.isMessageLoaded())
    {
      var data = timelineFactory.getMessageData();
      $scope.data.ticket = {"title": data.title, "description": data.message };
      timelineFactory.clear();
    }
    else {
      $scope.data.ticket = null;
    }
    $scope.data.bugtracker_new = true;
    $scope.data.onLoad = false;
    $scope.data.message = "_valid";
    $scope.data.onLoad = false;
  }

  // Get ticket related comments
  var getComments = function() {
    $http.get($rootScope.api.url + '/bugtracker/getcomments/' + $rootScope.user.token + '/' + $scope.projectID + '/' + $scope.ticketID)
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
    $http.get($rootScope.api.url + '/bugtracker/getprojecttags/' + $rootScope.user.token + '/' + $scope.projectID)
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
    $http.get($rootScope.api.url + '/dashboard/getprojectpersons/' + $rootScope.user.token + '/' + $scope.projectID)
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
  $scope.data.tagToAdd = [];
  $scope.data.tagToRemove = [];

  $scope.tagAdded = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.data.tagToRemove.length && index < 0; i++) {
      if ($scope.data.tagToRemove[i].id == tag.id)
        index = i;
    }

    if (index >= 0)
      $scope.data.tagToRemove.splice(index, 1);
    else
      $scope.data.tagToAdd.push(tag);
  };

  $scope.tagRemoved = function(tag) {
    var index = -1;
    for (var i = 0; i < $scope.data.tagToAdd.length && index < 0; i++) {
      if ($scope.data.tagToAdd[i].id == tag.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.tagToAdd.splice(index, 1);
    else
      $scope.data.tagToRemove.push(tag);
  };

  var memorizeTags = function() {
    var context = {"rootScope": $rootScope, "http": $http, "Notification": Notification, "scope": $scope};

    angular.forEach($scope.data.tagToAdd, function(tag) {
      if (!tag.id) {
        var data = {"data": {"token": context.rootScope.user.token, "projectId": context.scope.projectID, "name": tag.name}};
        context.http.post(context.rootScope.api.url + '/bugtracker/tagcreation', data)
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);
          },
          function errorCallback(resposne) {
              Notification.warning({ message: 'Unable to create tag: ' + tag.name + '. Please try again.', delay: 5000 });
          });
      }

      var data = {"data": {"token": context.rootScope.user.token, "bugId": context.scope.ticketID, "tagId": tag.id}};
      context.http.put(context.rootScope.api.url + '/bugtracker/assigntag', data)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            Notification.warning({ message: 'Unable to assign tag: ' + tag.name + '. Please try again.', delay: 5000 });
        });
    }, context);

    angular.forEach($scope.data.tagToRemove, function(tag) {
      context.http.delete(context.rootScope.api.url + '/bugtracker/removetag/' + context.rootScope.user.token + '/' + context.scope.ticketID + '/' + tag.id)
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
  $scope.data.userToAdd = [];
  $scope.data.userToRemove = [];

  $scope.userAdded = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.data.userToRemove.length && index < 0; i++) {
      if ($scope.data.userToRemove[i].user_id == user.user_id || $scope.data.userToRemove[i].id == user.user_id)
        index = i;
    }
    if (index >= 0)
      $scope.data.userToRemove.splice(index, 1);
    else
      $scope.data.userToAdd.push(user);
  };

  $scope.userRemoved = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.data.userToAdd.length && index < 0; i++) {
      if ($scope.data.userToAdd[i].user_id == user.user_id || $scope.data.userToAdd[i].user_id == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.userToAdd.splice(index, 1);
    else
      $scope.data.userToRemove.push(user);
  };

  var memorizeUsers = function() {
    var toAdd = [];
    angular.forEach($scope.data.userToAdd, function(user) {
      toAdd.push(user.user_id);
    }, toAdd);
    var toRemove = [];
    angular.forEach($scope.data.userToRemove, function(user) {
      toRemove.push(user.id);
    }, toRemove);

    var data = {"data": {"token": $rootScope.user.token, "bugId": $scope.ticketID, "toAdd": toAdd, "toRemove": toRemove}};

    Notification.info({ message: 'Saving users...', delay: 5000 });
    $http.put($rootScope.api.url + '/bugtracker/setparticipants', data)
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
  $scope.data.editMode = {};
  $scope.data.editMode['ticket'] = false;

  $scope.bugtracker_switchEditMode = function(elem) {
    $scope.data.editMode[elem] = ($scope.data.editMode[elem] ? false : true);
  };

  // TODO check edition rigths of the user

  // ------------------------------------------------------
  //                    TICKET
  // ------------------------------------------------------
  $scope.createTicket = function(ticket) {
    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "title": ticket.title,
                "description": ticket.description,
                "stateId": 1,
                "stateName": ""
                };
    var data = {"data": elem};

    Notification.info({ message: 'Posting ticket...', delay: 5000 });
    $http.post($rootScope.api.url + '/bugtracker/postticket', data)
      .then(function successCallback(response) {
        // $scope.data.message = "_valid";
        // $scope.data.bugtracker_new = false;
        $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.ticketID = $scope.data.ticket.id;
        memorizeTags();
        memorizeUsers();
        Notification.success({ message: 'Ticket posted', delay: 5000 });
        $location.path('/bugtracker/' + $scope.projectID + '/' + $scope.ticketID);
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to post comment. Please try again.', delay: 5000 });
      }, $scope);
  };


  $scope.editTicket = function(ticket) {
    var elem = {"token": $rootScope.user.token,
                "bugId": ticket.id,
                "title": ticket.title,
                "description": ticket.description,
                "stateId": 1,
                "stateName": ""};
    var data = {"data": elem};

    Notification.info({ message: 'Saving ticket...', delay: 5000 });
    $http.put($rootScope.api.url + '/bugtracker/editticket', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Ticket saved', delay: 5000 });
        memorizeTags();
        memorizeUsers();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to save ticket. Please try again.', delay: 5000 });
      });
      $scope.data.editMode['ticket'] = false;
  };



  $scope.closeTicket = function() {
    Notification.info({ message: 'Closing ticket ...', delay: 5000 });
    $http.delete($rootScope.api.url + '/bugtracker/closeticket/' + $rootScope.user.token + '/' + $scope.ticketID)
      .then(function successCallback(response) {
          Notification.success({ message: 'Ticket closed', delay: 5000 });
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to close ticket. Please try again.', delay: 5000 });
      });
  };

  $scope.reopenTicket = function() {
    Notification.info({ message: 'Reopening ticket ...', delay: 5000 });
    $http.put($rootScope.api.url + '/bugtracker/reopenticket/' + $rootScope.user.token + '/' + $scope.ticketID)
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
  $scope.editComment = function(comment) {
    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "commentId": comment.id,
                "title": comment.title,
                "description": comment.description
                };
    var data = {"data": elem};

    Notification.info({ message: 'Saving comment...', delay: 5000 });
    $http.put($rootScope.api.url + '/bugtracker/editcomment', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Comment saved', delay: 5000 });
        $scope.data.editMode[comment.id] = false;
        getComments();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to save comment. Please try again.', delay: 5000 });
        $scope.data.editMode[comment.id] = false;
        getComments();
      });

  };

  $scope.createComment = function(new_comment) {
    var elem = {"token": $rootScope.user.token,
                "projectId": $scope.projectID,
                "parentId": $scope.ticketID,
                "title": new_comment.title,
                "description": new_comment.description
                };
    var data = {"data": elem};

    Notification.info({ message: 'Posting comment...', delay: 5000 });
    $http.post($rootScope.api.url + '/bugtracker/postcomment', data)
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

  $scope.deleteComment = function(comment_id) {
    $http.delete($rootScope.api.url + '/bugtracker/closeticket/' + $rootScope.user.token + '/' + comment_id)
      .then(function successCallback(response) {
          Notification.success({ message: 'Comment deleted', delay: 5000 });
          getComments();
      },
      function errorCallback(resposne) {
          Notification.warning({ message: 'Unable to delete comment. Please try again.', delay: 5000 });
      });
  };

}]);
