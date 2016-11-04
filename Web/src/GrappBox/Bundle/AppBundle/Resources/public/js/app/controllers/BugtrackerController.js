/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP bugtracker
app.controller("BugtrackerController", ["$http", "$location", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope", "timelineIssueFactory",
    function($http, $location, notificationFactory, $rootScope, $route, $routeParams, $scope, timelineIssueFactory) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  //Scope variables initialization
  $scope.projectID = $routeParams.project_id;
  $scope.ticketID = $routeParams.id;

  $scope.data = { onLoad: true, bugtracker_new: false, canEdit: true, ticket: { }, tags: [], users: [], message: "_invalid" };

  //Get bugtracker informations set up data
  if ($scope.ticketID != 0) {
    $http.get($rootScope.api.url + "/bugtracker/getticket/" + $rootScope.user.token + "/" + $scope.ticketID)
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
            $rootScope.reject();
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
    if (timelineIssueFactory.isMessageLoaded())
    {
      var data = timelineIssueFactory.getMessageData();
      $scope.data.ticket = {"title": data.title, "description": data.message };
      timelineIssueFactory.clear();
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
    $http.get($rootScope.api.url + "/bugtracker/getcomments/" + $rootScope.user.token + "/" + $scope.projectID + "/" + $scope.ticketID)
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
    $http.get($rootScope.api.url + "/bugtracker/getprojecttags/" + $rootScope.user.token + "/" + $scope.projectID)
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
    $http.get($rootScope.api.url + "/dashboard/getprojectpersons/" + $rootScope.user.token + "/" + $scope.projectID)
      .then(function successCallback(response) {
        $scope.usersList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        angular.forEach($scope.usersList, function(user){

          user["name"] = user.first_name + " " + user.last_name;

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
    var context = {"rootScope": $rootScope, "http": $http, "notificationFactory": notificationFactory, "scope": $scope};

    angular.forEach($scope.data.tagToAdd, function(tag) {
      if (!tag.id) {
        var data = {"data": {"token": context.rootScope.user.token, "projectId": context.scope.projectID, "name": tag.name}};
        context.http.post(context.rootScope.api.url + "/bugtracker/tagcreation", data)
          .then(function successCallback(response) {
              tag.id = (response.data.data.id);

              var data = {"data": {"token": context.rootScope.user.token, "bugId": context.scope.ticketID, "tagId": tag.id}};
              context.http.put(context.rootScope.api.url + "/bugtracker/assigntag", data)
                .then(function successCallback(response) {

                },
                function errorCallback(resposne) {
                    notificationFactory.warning("Unable to assign tag: " + tag.name + ". Please try again.");
                });
          },
          function errorCallback(resposne) {
              notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
          });
      }
    }, context);

    angular.forEach($scope.data.tagToRemove, function(tag) {
      context.http.delete(context.rootScope.api.url + "/bugtracker/removetag/" + context.rootScope.user.token + "/" + context.scope.ticketID + "/" + tag.id)
        .then(function successCallback(response) {

        },
        function errorCallback(resposne) {
            notificationFactory.warning("Unable to remove tag: " + tag.name + ". Please try again.");
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

    $http.put($rootScope.api.url + "/bugtracker/setparticipants", data)
      .then(function successCallback(response) {
          notificationFactory.success("Users saved");
      },
      function errorCallback(resposne) {
          notificationFactory.warning("Unable to save users. Please try again.");
      });

  };

  // ------------------------------------------------------
  //                EDITION SWITCH
  // ------------------------------------------------------
  $scope.data.editMode = {};
  $scope.data.editMode["ticket"] = false;

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
                "stateName": "",
                "clientOrigin": false
                };
    var data = {"data": elem};

    $http.post($rootScope.api.url + "/bugtracker/postticket", data)
      .then(function successCallback(response) {
        // $scope.data.message = "_valid";
        // $scope.data.bugtracker_new = false;
        $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.ticketID = $scope.data.ticket.id;
        memorizeTags();
        memorizeUsers();
        notificationFactory.success("Ticket posted");
        $location.path("/bugtracker/" + $scope.projectID + "/" + $scope.ticketID);
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to post comment. Please try again.");
      }, $scope);
  };


  $scope.editTicket = function(ticket) {
    var elem = {"token": $rootScope.user.token,
                "bugId": ticket.id,
                "title": ticket.title,
                "description": ticket.description,
                "stateId": 1,
                "stateName": "",
                "clientOrigin": false
              };
    var data = {"data": elem};

    $http.put($rootScope.api.url + "/bugtracker/editticket", data)
      .then(function successCallback(response) {
        notificationFactory.success("Ticket saved");
        memorizeTags();
        memorizeUsers();
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to save ticket. Please try again.");
      });
      $scope.data.editMode["ticket"] = false;
  };



  $scope.closeTicket = function() {
    $http.delete($rootScope.api.url + "/bugtracker/closeticket/" + $rootScope.user.token + "/" + $scope.ticketID)
      .then(function successCallback(response) {
          notificationFactory.success("Ticket closed");
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          notificationFactory.warning("Unable to close ticket. Please try again.");
      });
  };

  $scope.reopenTicket = function() {
    Notification.info({ message: "Reopening ticket ...", delay: 5000 });
    $http.put($rootScope.api.url + "/bugtracker/reopenticket/" + $rootScope.user.token + "/" + $scope.ticketID)
      .then(function successCallback(response) {
          notificationFactory.success("Ticket reopened");
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          notificationFactory.warning("Unable to reopen ticket. Please try again.");
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

    $http.put($rootScope.api.url + "/bugtracker/editcomment", data)
      .then(function successCallback(response) {
        notificationFactory.success("Comment saved");
        $scope.data.editMode[comment.id] = false;
        getComments();
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to save comment. Please try again.");
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

    $http.post($rootScope.api.url + "/bugtracker/postcomment", data)
      .then(function successCallback(response) {
        notificationFactory.success("Comment posted");
        new_comment.title = "";
        new_comment.description = "";
        getComments();
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to post comment. Please try again.");
      }, new_comment);
  };

  $scope.deleteComment = function(comment_id) {
    $http.delete($rootScope.api.url + "/bugtracker/closeticket/" + $rootScope.user.token + "/" + comment_id)
      .then(function successCallback(response) {
          notificationFactory.success("Comment deleted");
          getComments();
      },
      function errorCallback(resposne) {
          notificationFactory.warning("Unable to delete comment. Please try again.");
      });
  };

}]);
