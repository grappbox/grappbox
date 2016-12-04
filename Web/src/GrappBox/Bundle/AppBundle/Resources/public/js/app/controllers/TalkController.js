/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP talk
app.controller("TalkController", ["accessFactory", "$http", "$location", "notificationFactory", "$rootScope", "$route", "$scope", "talkFactory", "$uibModal",
  function(accessFactory, $http, $location, notificationFactory, $rootScope, $route, $scope, talkFactory, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.route = { project_id: $route.current.params.project_id, talklist_id: $route.current.params.talklist_id, talk_id: $route.current.params.talk_id };
  $scope.creation = ($scope.route.talk_id == "new" ? true : false);

  $scope.talks = { loaded: false, valid: false, authorized: false, data: "", add: "", edit: "", delete: "", convert: "", found: false };
  $scope.comments = { loaded: false, valid: false, authorized: false, data: "", add: "" , edit: "", delete: "" };
  $scope.new = { loaded: false, valid: false, authorized: false, title: "", body: "", comment: "", disabled: false, error: { title: false, body: false } };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Get talk content
  var _getTalk = function() {
    $http.get($rootScope.api.url + "/timeline/messages/" + $scope.route.talklist_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function talkReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.11.1":
            for (var i = 0; i < response.data.data.array.length; ++i) {
              if (response.data.data.array[i].id == $scope.route.talk_id) {
                $scope.talks.data = response.data.data.array[i];
                $scope.talks.found = true;
              }
            }
            if (!$scope.talks.found) {
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.warning("This talk doesn't exist.");
            }
            $scope.talks.valid = true;
            $scope.talks.authorized = true;
            $scope.talks.loaded = true;
            break;

            case "1.11.3":
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.warning("This talk doesn't exist.");
            break;

            default:
            $scope.talks.data = null;
            $scope.talks.valid = false;
            $scope.talks.authorized = true;
            $scope.talks.loaded = true;
            break;
          }
        }
        else {
          $scope.talks = null;
          $scope.talks.valid = false;
          $scope.talks.authorized = true;
          $scope.talks.loaded = true;
        }
      },
      function talkNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "11.4.3":
            $rootScope.reject();
            break;

            case "11.4.9":
            $scope.talks.data = null;
            $scope.talks.valid = false;
            $scope.talks.authorized = false;
            $scope.talks.loaded = true;
            break;

            default:
            $scope.talks.data = null;
            $scope.talks.valid = false;
            $scope.talks.authorized = true;
            $scope.talks.loaded = true;
            break;
          }
        }
        else {
          $scope.talks.data = null;
          $scope.talks.valid = false;
          $scope.talks.authorized = true;
          $scope.talks.loaded = true;
        }
      }
    );
  };

  // Routine definition (local)
  // Get talk comments
  var _getTalkComments = function() {
    $http.get($rootScope.api.url + "/timeline/message/comments/" + $scope.route.talklist_id + "/" + $scope.route.talk_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function talkCommentsReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.11.1":
            $scope.comments.data = (response.data && response.data.data && response.data.data.array ? response.data.data.array : null);
            $scope.comments.valid = true;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
            break;

            case "1.11.3":
            $scope.comments.data = null;
            $scope.comments.valid = true;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
            break;

            default:
            $scope.comments.data = null;
            $scope.comments.valid = false;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
            break;
          }
        }
        else {
          $scope.comments.data = null;
          $scope.comments.valid = false;
          $scope.comments.authorized = true;
          $scope.comments.loaded = true;
        }
      },
      function talkCommentsNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "11.6.3":
            $rootScope.reject();
            break;

            case "11.6.4":
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.warning("This talk doesn't exist.");
            break;

            case "11.6.9":
            $scope.comments.data = null;
            $scope.comments.valid = false;
            $scope.comments.authorized = false;
            $scope.comments.loaded = true;
            break;

            default:
            $scope.comments.data = null;
            $scope.comments.valid = false;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
            break;
          }
        }
        else {
          $scope.comments.data = null;
          $scope.comments.valid = false;
          $scope.comments.authorized = true;
          $scope.comments.loaded = true;
        }
      }
    );
  };



  /* ==================== ADD TALK/TALK COMMENT ==================== */

  // Routine definition (scope)
  // Add new talk
  $scope.talks.add = function() {
    if (!$scope.new.disabled && $scope.new.title && $scope.new.body) {
      $scope.new.disabled = true;
      $http.post($rootScope.api.url + "/timeline/message/" + $scope.route.talklist_id,
        { data: { title: $scope.new.title, message: $scope.new.body }},
        { headers: { "Authorization": $rootScope.user.token }}).then(
        function talkPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "1.11.1":
              $scope.new.disabled = false;
              $location.path("talk/" + $scope.route.project_id + "/" + $scope.route.talklist_id + "/" + response.data.data.id);
              notificationFactory.success("Talk created.");
              break;

              default:
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.error();
              break;
            }
          }
          else {
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.error();
          }
        },
        function talkNotPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "11.2.3":
              $rootScope.reject();
              break;

              case "11.2.4":
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.warning("This talk list doesn't exist.");
              break;

              case "11.2.9":
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.warning("You don't have permission to create a new talk on this project.");
              break;

              default:
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.error();
              break;
            }
          }
          else {
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.error();
          }
        }
      );
    }
  };

  // Routine definition (scope)
  // Add new talk comment
  $scope.comments.add = function() {
    if (!$scope.new.disabled && $scope.new.comment) {
      $scope.new.disabled = true;
      $http.post($rootScope.api.url + "/timeline/comment/" + $scope.route.talklist_id,
        { data: { token: $rootScope.user.token, comment: $scope.new.comment, commentedId: $scope.route.talk_id }},
        { headers: { "Authorization": $rootScope.user.token }}).then(
        function talkCommentPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "1.11.1":
              _getTalkComments();
              $scope.new.disabled = false;
              $scope.new.comment = "";
              break;

              default:
              $scope.comments.data = null;
              $scope.comments.valid = false;
              $scope.comments.authorized = true;
              $scope.comments.loaded = true;
              break;
            }
          }
          else {
            $scope.comments.data = null;
            $scope.comments.valid = false;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
          }
        },
        function talkCommentNotPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "11.8.3":
              $rootScope.reject();
              break;

              case "11.8.4":
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.warning("This talk doesn't exist.");
              break;

              case "11.8.9":
              notificationFactory.warning("You don't have permission to post on this talk.");
              break;

              default:
              $scope.comments.data = null;
              $scope.comments.valid = false;
              $scope.comments.authorized = true;
              $scope.comments.loaded = true;
              break;
            }
          }
          else {
            $scope.comments.data = null;
            $scope.comments.valid = false;
            $scope.comments.authorized = true;
            $scope.comments.loaded = true;
          }
        }
      );
    }
  };



  /* ==================== EDIT TALK/TALK COMMENT ==================== */

  // "Edit talk" button handler
  $scope.talks.edit = function(talk_data) {
    $scope.new.title = talk_data.title;
    $scope.new.body = talk_data.message;

    var talkEdition = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", scope: $scope, templateUrl: "talkEdition.html", controller: "TalkEditionController" });

    talkEdition.result.then(
      function talkEditionConfirmed(data) {
        $http.put($rootScope.api.url + "/timeline/message/" + $scope.route.talklist_id + "/" + $scope.route.talk_id,
          { data: { title: $scope.new.title, message: $scope.new.body }},
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function talkEdited() {
            _getTalk();
            notificationFactory.success("Talk edited.");
            $scope.new.title = "";
            $scope.new.body = "";
          },
          function talkNotEdited(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "11.3.3":
                $rootScope.reject();
                break;

                case "11.3.4":
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.warning("This talk doesn't exist.");
                break;

                case "11.3.9":
                notificationFactory.warning("You don't have permission to edit this talk.");
                break;

                default:
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.error();
                break;
              }
            }
          }
        ),
        function talkEditionCancelled() {
          $scope.new.title = "";
          $scope.new.body = "";
        }
      }
    );
  };

  // "Edit talk comment" button handler
  $scope.comments.edit = function(comment_data) {
    $scope.new.body = comment_data.comment;

    var talkCommentEdition = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", scope: $scope, templateUrl: "talkCommentEdition.html", controller: "TalkCommentEditionController" });

    talkCommentEdition.result.then(
      function talkCommentEditionConfirmed(data) {
        $http.put($rootScope.api.url + "/timeline/comment/" + $scope.route.talklist_id,
          { data: { commentId: comment_data.id, comment: $scope.new.body }},
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function talkCommentEdited() {
            _getTalkComments();
            notificationFactory.success("Comment edited.");
            $scope.new.body = "";
          },
          function talkCommentNotEdited(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "11.9.3":
                $rootScope.reject();
                break;

                case "11.9.4":
                $location.path("talk/" + $scope.route.project_id + "/" + $scope.route.talklist_id + "/" + $scope.route.talk_id);
                notificationFactory.warning("This comment doesn't exist.");
                break;

                case "11.9.9":
                notificationFactory.warning("You don't have permission to edit this comment.");
                break;

                default:
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.error();
                break;
              }
            }
          }
        ),
        function talkCommentEditionCancelled() {
          $scope.new.body = "";
        }
      }
    );
  };



  /* ==================== DELETE TALK/TALK COMMENT ==================== */

  // "Delete talk" button handler
  $scope.talks.delete = function(talk_id) {
    var talkDeletion = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "talkDeletion.html", controller: "TalkDeletionController" });

    talkDeletion.result.then(
      function talkDeletionConfirmed(data) {
        $http.delete($rootScope.api.url + "/timeline/message/" + $scope.route.talklist_id + "/" + talk_id,
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function talkDeleted() {
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.success("Talk deleted.");
          },
          function talkNotDeleted(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "11.6.3":
                $rootScope.reject();
                break;

                case "11.6.4":
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.warning("This talk doesn't exist.");
                break;

                case "11.6.9":
                notificationFactory.warning("You don't have permission to delete this talk.");
                break;

                default:
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.error();
                break;
              }
            }
          }
        ),
        function talkDeletionCancelled() {}
      }
    );
  };

  // "Delete talk comment" button handler
  $scope.comments.delete = function(comment_id) {
    var talkCommentDeletion = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "talkCommentDeletion.html", controller: "TalkCommentDeletionController" });

    talkCommentDeletion.result.then(
      function talkCommentDeletionConfirmed(data) {
        $http.delete($rootScope.api.url + "/timeline/comment/" + comment_id,
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function talkCommentDeleted() {
            _getTalkComments();
            notificationFactory.success("Comment deleted.");
          },
          function talkCommentNotDeleted(response) {
            if (response && response.data && response.data.info && response.data.info.return_code) {
              switch(response.data.info.return_code) {
                case "11.10.3":
                $rootScope.reject();
                break;

                case "11.10.4":
                _getTalkComments();
                notificationFactory.warning("This comment doesn't exist.");
                break;

                case "11.10.9":
                notificationFactory.warning("You don't have permission to delete this comment.");
                break;

                default:
                $location.path("talk/" + $scope.route.project_id);
                notificationFactory.error();
                break;
              }
            }
          }
        ),
        function talkCommentDeletionCancelled() {}
      }
    );
  };



  /* ==================== CONVERT TALK ==================== */

  // "Convert to issue" button handler
  $scope.talks.convert = function(talk_data) {
    talkFactory.clear();
    talkFactory.setMessage(talk_data);
    $location.path("/bugtracker/" + $scope.route.project_id + "/0");
  };



  /* ==================== EXECUTION ==================== */

  accessFactory.projectAvailable();
  if ($scope.route.talk_id != "new") {
    _getTalk();
    _getTalkComments();
  }

}]);



/**
* Controller definition (from view)
* Confirmation prompt for talk edition.
*
*/
app.controller("TalkEditionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.talkEditionConfirmed = function() {
    $scope.new.error.title = ($scope.new.title && $scope.new.title.length ? false : true);
    $scope.new.error.body = ($scope.new.body && $scope.new.body.length ? false : true);
    if (!$scope.new.error.title && !$scope.new.error.body)
      $uibModalInstance.close();
  };
  $scope.talkEditionCancelled = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* Confirmation prompt for talk comment edition.
*
*/
app.controller("TalkCommentEditionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.talkCommentEditionConfirmed = function() {
    $scope.new.error.body = ($scope.new.body && $scope.new.body.length ? false : true);
    if (!$scope.new.error.body)
      $uibModalInstance.close();
  };
  $scope.talkCommentEditionCancelled = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* Confirmation prompt for talk deletion.
*
*/
app.controller("TalkDeletionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.talkDeletionConfirmed = function() { $uibModalInstance.close(); };
  $scope.talkDeletionCancelled = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* Confirmation prompt for talk comment deletion.
*
*/
app.controller("TalkCommentDeletionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.talkCommentDeletionConfirmed = function() { $uibModalInstance.close(); };
  $scope.talkCommentDeletionCancelled = function() { $uibModalInstance.dismiss(); };
}]);