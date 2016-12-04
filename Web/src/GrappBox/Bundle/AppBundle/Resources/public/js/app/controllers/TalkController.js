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

  $scope.talk = { loaded: false, valid: false, authorized: false, data: "", add: "", edit: "", delete: "", found: false };
  $scope.comment = { loaded: false, valid: false, authorized: false, data: "", add: "" , edit: "", delete: "" };
  $scope.new = { loaded: false, valid: false, authorized: false, title: "", body: "", disabled: false };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Get talk content
  var _getTalk = function() {
    $scope.talk.found = false;
    $http.get($rootScope.api.url + "/timeline/messages/" + $scope.route.talklist_id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function talkReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "1.11.1":
            for (var i = 0; i < response.data.data.array.length; ++i) {
              if (response.data.data.array[i].id == $scope.route.talk_id) {
                $scope.talk.data = response.data.data.array[i];
                $scope.talk.found = true;
              }
            }
            if (!$scope.talk.found) {
              $location.path("talk/" + $scope.route.project_id);
              notificationFactory.warning("This talk doesn't exist.");
            }
            $scope.talk.valid = true;
            $scope.talk.authorized = true;
            $scope.talk.loaded = true;
            break;

            case "1.11.3":
            $location.path("talk/" + $scope.route.project_id);
            notificationFactory.warning("This talk doesn't exist.");
            break;

            default:
            $scope.talk.data = null;
            $scope.talk.valid = false;
            $scope.talk.authorized = true;
            $scope.talk.loaded = true;
            break;
          }
        }
        else {
          $scope.talk = null;
          $scope.talk.valid = false;
          $scope.talk.authorized = true;
          $scope.talk.loaded = true;
        }
      },
      function talkNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code) {
          switch(response.data.info.return_code) {
            case "11.4.3":
            $rootScope.reject();
            break;

            case "11.4.9":
            $scope.talk.data = null;
            $scope.talk.valid = false;
            $scope.talk.authorized = false;
            $scope.talk.loaded = true;
            break;

            default:
            $scope.talk.data = null;
            $scope.talk.valid = false;
            $scope.talk.authorized = true;
            $scope.talk.loaded = true;
            break;
          }
        }
        else {
          $scope.talk.data = null;
          $scope.talk.valid = false;
          $scope.talk.authorized = true;
          $scope.talk.loaded = true;
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
            $scope.comment.data = (response.data && response.data.data && response.data.data.array ? response.data.data.array : null);
            $scope.comment.valid = true;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
            break;

            case "1.11.3":
            $scope.comment.data = null;
            $scope.comment.valid = true;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
            break;

            default:
            $scope.comment.data = null;
            $scope.comment.valid = false;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
            break;
          }
        }
        else {
          $scope.comment.data = null;
          $scope.comment.valid = false;
          $scope.comment.authorized = true;
          $scope.comment.loaded = true;
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
            $scope.comment.data = null;
            $scope.comment.valid = false;
            $scope.comment.authorized = false;
            $scope.comment.loaded = true;
            break;

            default:
            $scope.comment.data = null;
            $scope.comment.valid = false;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
            break;
          }
        }
        else {
          $scope.comment.data = null;
          $scope.comment.valid = false;
          $scope.comment.authorized = true;
          $scope.comment.loaded = true;
        }
      }
    );
  };



  /* ==================== ADD TALK/TALK COMMENT ==================== */

  // Routine definition (scope)
  // Add new talk
  $scope.talk.add = function() {
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
  $scope.comment.add = function() {
    if (!$scope.comment.disabled && $scope.new.body) {
      $scope.new.disabled = true;
      $http.post($rootScope.api.url + "/timeline/comment/" + $scope.route.talklist_id,
        { data: { token: $rootScope.user.token, comment: $scope.new.body, commentedId: $scope.route.talk_id }},
        { headers: { "Authorization": $rootScope.user.token }}).then(
        function talkCommentPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "1.11.1":
              _getTalkComments();
              $scope.new.disabled = false;
              $scope.new.body = "";
              break;

              default:
              $scope.comment.data = null;
              $scope.comment.valid = false;
              $scope.comment.authorized = true;
              $scope.comment.loaded = true;
              break;
            }
          }
          else {
            $scope.comment.data = null;
            $scope.comment.valid = false;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
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
              $scope.comment.data = null;
              $scope.comment.valid = false;
              $scope.comment.authorized = true;
              $scope.comment.loaded = true;
              break;
            }
          }
          else {
            $scope.comment.data = null;
            $scope.comment.valid = false;
            $scope.comment.authorized = true;
            $scope.comment.loaded = true;
          }
        }
      );
    }
  };



  /* ==================== DELETE TALK/TALK COMMENT ==================== */

  // "Delete talk" button handler
  $scope.talk.delete = function(talk_id) {
    var talkDeletion = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "talkDeletion.html", controller: "TalkDeletionController" });

    talkDeletion.result.then(
      function talkDeletionConfirmed(data) {
        $http.delete($rootScope.api.url + "/timeline/message/" + $scope.route.talklist_id + "/" + talk_id,
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function talkDeleted(response) {
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



/* ==================== EXECUTION ==================== */

  accessFactory.projectAvailable();
  if ($scope.route.talk_id != "new") {
    _getTalk();
    _getTalkComments();
  }

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