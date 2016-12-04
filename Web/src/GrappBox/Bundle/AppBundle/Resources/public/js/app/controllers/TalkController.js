/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP talk
app.controller("TalkController", ["accessFactory", "$http", "$location", "notificationFactory", "$q", "$rootScope", "$route", "$scope", "talkFactory", "$uibModal",
    function(accessFactory, $http, $location, notificationFactory, $q, $rootScope, $route, $scope, talkFactory, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.route = { project_id: $route.current.params.project_id, talklist_id: $route.current.params.talklist_id, talk_id: $route.current.params.talk_id };
  $scope.talk = { loaded: false, valid: false, authorized: false, data: "", found: false };
  $scope.comments = { loaded: false, valid: false, authorized: false, data: "", add: "", new: "", disabled: false };



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



  /* ==================== SCOPE ROUTINES ==================== */

  $scope.comments.add = function() {
    if (!$scope.comments.disabled && $scope.comments.new) {
      $scope.comments.disabled = true;
      $http.post($rootScope.api.url + "/timeline/comment/" + $scope.route.talklist_id,
        { data: { token: $rootScope.user.token, comment: $scope.comments.new, commentedId: $scope.route.talk_id } },
        { headers: { "Authorization": $rootScope.user.token }}).then(
        function talkCommentsPosted(response) {
          if (response && response.data && response.data.info && response.data.info.return_code) {
            switch(response.data.info.return_code) {
              case "1.11.1":
              _getTalkComments();
              $scope.comments.disabled = false;
              $scope.comments.new = "";
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
        function talkCommentsNotPosted(response) {
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


  /* ==================== EXECUTION ==================== */

  accessFactory.projectAvailable();

  _getTalk();
  _getTalkComments();
  
}]);