/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP bugtracker
app.controller("BugtrackerController", ["$http", "$location", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope", "$uibModal", "talkFactory",
    function($http, $location, notificationFactory, $rootScope, $route, $routeParams, $scope, $uibModal, talkFactory) {

  // ------------------------------------------------------
  //                PAGE IGNITIALIZATION
  // ------------------------------------------------------

  // TODO: check edition right

  //Scope variables initialization
  $scope.projectId = $routeParams.project_id;
  $scope.ticketID = $routeParams.id;
  $scope.userId = $rootScope.user.id;

  $scope.data = { onLoad: true, bugtracker_new: false, canEdit: true, edit: { }, ticket: { }, comment: "", tags: [], users: [], message: "_invalid" };

  //Get bugtracker informations set up data
  if ($scope.ticketID != 0) {
    $http.get($rootScope.api.url + '/bugtracker/ticket/' + $scope.ticketID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.message = (response.data.info && response.data.info.return_code == "1.4.1" ? "_valid" : "_empty");
        $scope.data.onLoad = false;
        formatUsers();
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
    if (talkFactory.isMessageLoaded())
    {
      var data = talkFactory.getMessageData();
      $scope.data.ticket = {"title": data.title, "description": data.message };
      talkFactory.clear();
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
    $http.get($rootScope.api.url + '/bugtracker/comments/' + $scope.ticketID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.commentsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      },
      function errorCallback(response) {
        $scope.commentsList = null;
      });
  };
  if ($scope.ticketID != 0) {
    getComments();
  }


  // ------------------------------------------------------//
  //                    DISPLAY HELP                       //
  // ------------------------------------------------------//

  //-----------------DATE FORMATING----------------------//
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(" ")) : "N/A");
  };

  //----------------USERS FORMATING---------------------//
  var formatUsers = function() {
    angular.forEach($scope.data.ticket.users, function(user) {
      user["name"] = user.firstname + " " + user.lastname;
    });
  };

  //------------------EDITION SWITCH-----------------------//
  $scope.data.editMode = {};
  $scope.data.editMode['ticket'] = false;

  var setTicketEditableContent = function(){
    $scope.data.tagToAdd = [];
    $scope.data.tagToRemove = [];
    $scope.data.userToAdd = [];
    $scope.data.userToRemove = [];
    $scope.data.edit.title = $scope.data.ticket.title;
    $scope.data.edit.description = $scope.data.ticket.description;
  };
  //
  // var setCommentEditableContent = function(obj){
  //   $scope.data.edit.comment.title = obj.title;
  //   $scope.data.edit.comment.description = obj.description;
  // };

  $scope.bugtracker_switchEditMode = function(elem) {
    if (elem == 'ticket') {
      setTicketEditableContent();
    }
    else {
      //setCommentEditableContent(obj);
    }
    $scope.data.editMode[elem] = ($scope.data.editMode[elem] ? false : true);
  };

  // ------------------------------------------------------//
  //            TICKET EDITION MANAGEMENT                  //
  // ------------------------------------------------------//

  //----------------TAGS ASSIGNATION----------------------//
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

  // Get all users from the project
  var getProjectTags = function() {
    $http.get($rootScope.api.url + '/bugtracker/project/tags/' + $scope.projectId, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        console.log('response.data', response.data.data);
        $scope.tagsList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : []);
        console.log("tagslist", $scope.tagsList.length);
      },
      function errorCallback(response) {
        $scope.tagsList = [];
      });
  };
  getProjectTags();

  //----------------TAGS CREATION----------------------//

  $scope.newTag = "";
  // "Create Tag" button handler
  $scope.onNewTag = function() {

    var modal_createNewBugTag = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_createNewBugTag.html", controller: "modal_createNewBugTag" });
    modal_createNewBugTag.result.then(
      function onModalConfirm() {

        var random_color = Math.floor(Math.random()*16777215).toString(16);
        var data = {"data": {"projectId": $scope.projectId, "name": $scope.newTag, "color": random_color }};

        $http.post($rootScope.api.url + "/bugtracker/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
            .then(function successCallback(response) {
              notificationFactory.success("Tag created succesfuly.");
              $scope.tagsList.push(response.data.data);
            },
            function errorCallback(response) {
              notificationFactory.warning("Unable to create tag: " + data.name + ". Please try again.");
            })

        ,function onModalDismiss() { }
      }
    );
  };


  //----------------USERS ASSIGNATION----------------------//
  $scope.data.userToAdd = [];
  $scope.data.userToRemove = [];

  $scope.userAdded = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.data.userToRemove.length && index < 0; i++) {
      if ($scope.data.userToRemove[i] == user.id || $scope.data.userToRemove[i] == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.userToRemove.splice(index, 1);
    else
      $scope.data.userToAdd.push(user.id);
  };

  $scope.userRemoved = function(user) {
    var index = -1;
    for (var i = 0; i < $scope.data.userToAdd.length && index < 0; i++) {
      if ($scope.data.userToAdd[i] == user.id || $scope.data.userToAdd[i] == user.id)
        index = i;
    }
    if (index >= 0)
      $scope.data.userToAdd.splice(index, 1);
    else
      $scope.data.userToRemove.push(user.id);
  };

  // Get all users from the project
  var getProjectUsers = function() {
    $http.get($rootScope.api.url + '/project/users/' + $scope.projectId, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.usersList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : []);
        angular.forEach($scope.usersList, function(user){
          user['name'] = user.firstname + " " + user.lastname;
        });
      },
      function errorCallback(response) {
        $scope.usersList = [];
      });
  };
  getProjectUsers();


  // ------------------------------------------------------
  //                    TICKET
  // ------------------------------------------------------

  $scope.createTicket = function(ticket) {
      var elem = {"projectId": $scope.projectId,
                  "title": ticket.title,
                  "description": ticket.description ? ticket.description : "",
                  "clientOrigin": false,
                  "tags": [],
                  "users": $scope.data.userToAdd.length ? $scope.data.userToAdd : []
                  };

      // if ($scope.data.tagToAdd) {
      //   var newTags = [];
      //   for (var i = 0; i < $scope.data.tagToAdd.length; i++) {
      //     if (!$scope.data.tagToAdd[i].id) {
      //       var random_color = Math.floor(Math.random()*16777215).toString(16);
      //       var data = {"data": {"projectId": $scope.projectId, "name": $scope.data.tagToAdd[i].name, "color": random_color }};
      //       $http.post($rootScope.api.url + "/bugtracker/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
      //         .then(function successCallback(response) {
      //             newTags.push(response.data.data.id);
      //         },
      //         function errorCallback(response) {
      //             notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
      //         });
      //     } else {
      //       newTags.push($scope.data.tagToAdd[i].id);
      //     }
      //   }
      //   elem.tags = newTags;
      // }

      for (var i = 0; i < $scope.data.tagToAdd.length; i++) {
        elem.tags.push($scope.data.tagToAdd[i].id)
      }

      var data = {"data": elem};

      $http.post($rootScope.api.url + '/bugtracker/ticket', data, {headers: { 'Authorization': $rootScope.user.token }})
        .then(function successCallback(response) {
          $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.ticketID = $scope.data.ticket.id;
          notificationFactory.success('Issue posted');
          $location.path('/bugtracker/' + $scope.projectId + '/' + $scope.ticketID);
        },
        function errorCallback(response) {
          notificationFactory.error('Unable to post issue. Please try again.');
        }, $scope);
    };

  $scope.editTicket = function(ticket) {
    var elem = {"title": ticket.title,
                "description": ticket.description ? ticket.description : null,
                "clientOrigin": false,
                "addTags": [],
                "removeTags" : $scope.data.tagToRemove.length ? $scope.data.tagToRemove : [],
                "addUsers": $scope.data.userToAdd.length ? $scope.data.userToAdd : [],
                "removeUsers": $scope.data.userToRemove.length ? $scope.data.userToRemove : []
              };

    // if ($scope.data.tagToAdd) {
    //   var newTags = [];
    //   for (var i = 0; i < $scope.data.tagToAdd.length; i++) {
    //     if (!$scope.data.tagToAdd[i].id) {
    //       var random_color = Math.floor(Math.random()*16777215).toString(16);
    //       var data = {"data": {"projectId": $scope.projectId, "name": $scope.data.tagToAdd[i].name, "color": random_color }};
    //
    //       $http.post($rootScope.api.url + "/bugtracker/tag", data, {headers: { 'Authorization': $rootScope.user.token }})
    //         .then(function successCallback(response) {
    //             newTags.push(response.data.data.id);
    //         },
    //         function errorCallback(response) {
    //             notificationFactory.warning("Unable to create tag: " + tag.name + ". Please try again.");
    //         });
    //     } else {
    //       newTags.push($scope.data.tagToAdd[i].id);
    //     }
    //   }
    //   elem.addTags = newTags;
    // }
    for (var i = 0; i < $scope.data.tagToAdd.length; i++) {
      elem.addTags.push($scope.data.tagToAdd[i].id)
    }

    var data = {"data": elem};

    $http.put($rootScope.api.url + '/bugtracker/ticket/' + $scope.ticketID, data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.data.ticket = $scope.data.ticket = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.editMode['ticket'] = false;
        formatUsers();
        notificationFactory.success('Issue saved');
      },
      function errorCallback(response) {
        $scope.data.editMode['ticket'] = false;
        notificationFactory.warning('Unable to save issue. Please try again.');
      });

  };

  $scope.closeTicket = function() {
    $http.delete($rootScope.api.url + "/bugtracker/ticket/close/" + $scope.ticketID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
          $location.path("bugtracker/" + $scope.projectId);
          notificationFactory.success("Ticket closed");
      },
      function errorCallback(resposne) {
          notificationFactory.error("Unable to close issue. Please try again.");
      });
  };

  $scope.reopenTicket = function() {
    $http.get($rootScope.api.url + "/bugtracker/ticket/reopen/" + $scope.ticketID, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
          notificationFactory.success("issue reopened");
          //$location.reload();
          $route.reload();
      },
      function errorCallback(resposne) {
          notificationFactory.error("Unable to reopen issue. Please try again.");
      });
  };

  // ------------------------------------------------------
  //                    COMMENTS
  // ------------------------------------------------------
  $scope.createComment = function(comment) {
    var elem = {"parentId": $scope.ticketID,
                "comment": comment};

    var data = {'data': elem};
    console.log(data);

    $http.post($rootScope.api.url + '/bugtracker/comment', data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        notificationFactory.success('Comment posted');
        //$scope.data.editMode[comment.id] = false;
        $scope.data.comment = "";
        getComments();
      },
      function errorCallback(response) {
        notificationFactory.error('Unable to save comment. Please try again.');
        $scope.data.editMode[comment.id] = false;
        getComments();
      });
  };

  $scope.editComment = function(comment) {
    var elem = {"comment": comment.comment};

    var data = {"data": elem};

    $http.put($rootScope.api.url + '/bugtracker/comment/'+comment.id, data, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        notificationFactory.success('Comment edited');
        $scope.data.editMode[comment.id] = false;
        getComments();
      },
      function errorCallback(response) {
        notificationFactory.error('Unable to save comment. Please try again.');
        $scope.data.editMode[comment.id] = false;
        getComments();
      });
  };

  $scope.deleteComment = function(comment_id) {
    $http.delete($rootScope.api.url + "/bugtracker/comment/" + comment_id, {headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
          notificationFactory.success("Comment deleted");
          getComments();
      },
      function errorCallback(resposne) {
          notificationFactory.error("Unable to delete comment. Please try again.");
      });
  };

}]);


// ------------------------------------------------------
//                    MODALS
// ------------------------------------------------------

/**
* Controller definition (from view)
* BUGTAG CREATION => new message form.
*
*/
app.controller("modal_createNewBugTag", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { name: false };

  $scope.modal_confirmTagCreation = function() {
    $scope.error.name = ($scope.newTag && $scope.newTag.length > 0 ? false : true);

    var hasErrors = false;
    angular.forEach($scope.error, function(value, key) {
      if (value)
        hasErrors = true;
    });
    if (!hasErrors)
      $uibModalInstance.close();
  };
  $scope.modal_cancelTagCreation = function() { $uibModalInstance.dismiss(); };
}]);
