/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP timeline
*
*/
app.controller("timelineController", ["$rootScope", "$scope", "$route", "$http", "$q", "$uibModal", "Notification", "timelineFactory", "$location",
    function($rootScope, $scope, $route, $http, $q, $uibModal, Notification, timelineFactory, $location) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.modal = { onLoad: true, valid: false, authorized: false };
	$scope.method = { switchTab: "", formatObjectDate: "" };

  $scope.timeline = { project_id: $route.current.params.project_id, team: {}, customer: {} };
  $scope.active = { message: { title: "", message: "" }, comment: { title: "", comment: "" }, modal: "" };
  $scope.action = { onOpenMessage: "", onNewMessage: "", onNewComment: "", onEditMessage: "", onEditComment: "", onDeleteMessage: "", onDeleteComment: "", onNewIssue: "" }
  
  $scope.new = { message: { title: "", message: "" }, comment: { title: "", comment: "" } };
  $scope.comments = {};
  


	/* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Reset timeline list
  var _resetTimelineList = function() {
    $scope.timeline.team = { id: "", typeId: "", active: true, onLoad: true, valid: false, messages: null };
    $scope.timeline.customer = { id: "", typeId: "", active: false, onLoad: true, valid: false, messages: null };
  };

  // Routine definition (local)
  // Reset modal content
  var _resetModalContent = function() {
    $scope.modal = { onLoad: true, valid: false, authorized: false };
    $scope.active.message = { title: "", message: "" };
    $scope.active.comment = { title: "", message: "" };
    $scope.new.message = { title: "", message: "" };
    $scope.new.comment = { title: "", message: "" };
    $scope.comments = {};
  };

  // Routine definition (local)
  // Get timeline list
  var _getTimelineList = function() {
  	var deferred = $q.defer();

  	$http.get($rootScope.api.url + "/timelines/" + $scope.timeline.project_id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function onGetTimelineListSucces(response) {
  			if (response.data.info) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					angular.forEach((response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null), function(value, key) {
  						if (value.typeId == "2")
  							$scope.timeline.team = { id: value.id, typeId: value.typeId, active: true, onLoad: true, valid: false, messages: null };
  						else
  							$scope.timeline.customer = { id: value.id, typeId: value.typeId, active: false, onLoad: true, valid: false, messages: null };
  					});
            $scope.view.authorized = true;
  					deferred.resolve();
  					break;

            // ISSUE #175 WORKAROUND
  					case "1.11.3":
            _resetTimelineList();
            $scope.timeline.team.onLoad = false;
            $scope.timeline.customer.onLoad = false;
            $scope.view.authorized = false;
  					deferred.reject();
  					break;

  					default:
            _resetTimelineList();
            $scope.timeline.team.onLoad = false;
            $scope.timeline.customer.onLoad = false;
  					deferred.reject();
  					break;
  				}
  			}
  			else {
          _resetTimelineList();
  				deferred.reject();
  			}
  			return deferred.promise;
  		},
  		function onGetTimelineListFail(response) {
  			if (response.data.info && response.data.info.return_code == "11.1.3")
  				$rootScope.onUserTokenError();
				$scope.timeline.team = null;
				$scope.timeline.customer = null;
  			deferred.reject();

				return deferred.promise;
  		}
  	);
		return deferred.promise;
  };

  // Routine definition (local)
  // Get selected timeline messages
  var _getTimelineMessages = function(timeline) {
    timeline.valid = false;
    timeline.onLoad = true;

  	$http.get($rootScope.api.url + "/timeline/messages/" + timeline.id, { headers: { "Authorization": $rootScope.user.token }}).then(
  		function onGetTimelineMessagesSuccess(response) {
  			if (response.data.info) {
  				switch(response.data.info.return_code) {
  					case "1.11.1":
  					timeline.messages = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
  					timeline.valid = true;
  					timeline.onLoad = false;
            $scope.view.authorized = true;
  					break;

  					case "1.11.3":
  					timeline.messages = null;
  					timeline.valid = true;
  					timeline.onLoad = false;
            $scope.view.authorized = true;
						break;

  					default:
						timeline.messages = null;
	  				timeline.valid = false;
  					timeline.onLoad = false;
  					break;
  				}
  			}
  			else {
					timeline.messages = null;
  				timeline.valid = false;
					timeline.onLoad = false;
  			}
  		},
  		function onGetTimelineMessagesFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "11.4.3":
            $rootScope.onUserTokenError();
            break;

            case "11.4.9":
            timeline.messages = null;
            timeline.valid = false;
            timeline.onLoad = false;
            $scope.view.authorized = false;
            break;

            default:
            timeline.messages = null;
            timeline.valid = false;
            timeline.onLoad = false;
            break;
          }
        }
        else {
          timeline.messages = null;
          timeline.valid = false;
          timeline.onLoad = false;
        }
  		}
		);
  };



  /* ==================== SCOPE ROUTINES ==================== */

	// Routine definition (scope)
  // Switch timeline tab
  $scope.method.switchTab = function(name) {
  	$scope.timeline.team.active = (name == "team" ? true : false);
  	$scope.timeline.customer.active = (name == "customer" ? true : false);
  };

  // Routine definition (scope)
  // Format object date
  $scope.method.formatObjectDate = function(dateToFormat) {
  	return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };



  /* ==================== EXECUTION ==================== */

  _resetTimelineList();
  
  var timelineList_promise = _getTimelineList();
  timelineList_promise.then(
  	function onPromiseGetSuccess() {
      $scope.timeline.team.messages = _getTimelineMessages($scope.timeline.team);
      $scope.timeline.customer.messages = _getTimelineMessages($scope.timeline.customer);
			$scope.view.valid = true;
			$scope.view.onLoad = false;
  	},
  	function onPromiseGetFail() {
			$scope.view.valid = false;
			$scope.view.onLoad = false;
  	}
  );



  /* ==================== VIEW OBJECT (OPEN MESSAGE) ==================== */

  // Routine definition (local)
  // Get message comments
  var _getMessageComments = function(message) {
    $http.get($rootScope.api.url + "/timeline/message/comments" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id) + "/" + message.id, { headers: { "Authorization": $rootScope.user.token }}).then(
      function onGetCommentsSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.11.1":
            $scope.comments = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
            $scope.modal.valid = true;
            $scope.modal.onLoad = false;
            $scope.modal.authorized = true;
            break;

            case "1.11.3":
            $scope.comments = null;
            $scope.modal.valid = true;
            $scope.modal.onLoad = false;
            $scope.modal.authorized = true;
            break;

            default:
            $scope.comments = null;
            $scope.modal.valid = false;
            $scope.modal.onLoad = false;
            $scope.modal.authorized = true;
            break;
          }
        }
        else {
          $scope.comments = null;
          $scope.modal.valid = false;
          $scope.modal.onLoad = false;
          $scope.modal.authorized = true;
        }
      },
      function onGetCommentsFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "11.6.3":
            $rootScope.onUserTokenError();
            break;

            case "11.6.9":
            $scope.comments = null;
            $scope.modal.valid = false;
            $scope.modal.onLoad = false;
            $scope.modal.authorized = false;
            break;

            default:
            $scope.comments = null;
            $scope.modal.valid = false;
            $scope.modal.onLoad = false;
            $scope.modal.authorized = true;
            break;
          }
        }
        else {
          $scope.comments = null;
          $scope.modal.valid = false;
          $scope.modal.onLoad = false;
          $scope.modal.authorized = true;
        }
      }
    );
  };

  // "Open message" button handler
  $scope.action.onOpenMessage = function(message) {
    _resetModalContent();
    _getMessageComments(message);

    $scope.active.message = message;
    $scope.active.modal = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_openMessage.html", controller: "modal_openMessage" });
  };



  /* ==================== CREATE OBJECT (CREATE MESSAGE/COMMENT/ISSUE) ==================== */

  // "Add message" button handler
  $scope.action.onNewMessage = function() {
    $scope.new.message.title = "";
    $scope.new.message.message = "";

    var modal_newMessage = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_createNewMessage.html", controller: "modal_createNewMessage" });
    modal_newMessage.result.then(
      function onModalConfirm() {
        $http.post($rootScope.api.url + "/timeline/message/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id),
        { headers: { "Authorization": $rootScope.user.token }},
        { data: { title: $scope.new.message.title, message: $scope.new.message.message }}).then(
          function onPostMessageSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else
              Notification.success({ title: "Timeline", message: "Message successfully posted.", delay: 2000 });
            if ($scope.timeline.team.active)
              $scope.timeline.team.messages = _getTimelineMessages($scope.timeline.team);
            else
              $scope.timeline.customer.messages = _getTimelineMessages($scope.timeline.customer);       
          },
          function onPostMessageFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.2.3":
                $rootScope.onUserTokenError();
                break;

                case "11.2.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };

  // "Add comment" button handler
  $scope.action.onNewComment = function(message) {
    $scope.new.comment.title = "";
    $scope.new.comment.message = "";

    var modal_newComment = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, windowClass: "submodal", templateUrl: "modal_createNewComment.html", controller: "modal_createNewComment" });
    modal_newComment.result.then(
      function onModalConfirm() {
        $http.post($rootScope.api.url + "/timeline/comment/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id),
        { headers: { "Authorization": $rootScope.user.token }},
        { data: { token: $rootScope.user.token, title: $scope.new.comment.title, message: $scope.new.comment.message, commentedId: message.id }}).then(
          function onPostCommentSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else
              Notification.success({ title: "Timeline", message: "Comment successfully posted.", delay: 2000 });
            _getMessageComments(message);
          },
          function onPostCommentFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.2.3":
                $rootScope.onUserTokenError();
                break;

                case "11.2.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };

  // "Convert to issue" button handler
  $scope.action.onNewIssue = function(message) {
    timelineFactory.clear();
    timelineFactory.setMessage(message);
    $location.path("/bugtracker/" + $scope.timeline.project_id + "/0");
  };



  /* ==================== EDIT OBJECT (EDIT MESSAGE/COMMENT) ==================== */

  // "Edit message" button handler
  $scope.action.onEditMessage = function(message) {
    $scope.new.message.title = message.title;
    $scope.new.message.message = message.message;

    var modal_editMessage = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, windowClass: "submodal", templateUrl: "modal_editMessage.html", controller: "modal_editMessage" });
    modal_editMessage.result.then(
      function onModalConfirm() {
        $http.put($rootScope.api.url + "/timeline/message/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id),
        { headers: { "Authorization": $rootScope.user.token }},
        { data: { token: $rootScope.user.token, messageId: message.id, title: $scope.new.message.title, message: $scope.new.message.message }}).then(
          function onPutMessageSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else {
              Notification.success({ title: "Timeline", message: "Message updated.", delay: 2000 });
              $scope.active.message.title = $scope.new.message.title;
              $scope.active.message.message = $scope.new.message.message;
            }
            if ($scope.timeline.team.active)
              $scope.timeline.team.messages = _getTimelineMessages($scope.timeline.team);
            else
              $scope.timeline.customer.messages = _getTimelineMessages($scope.timeline.customer);
          },
          function onPutMessageFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.3.3":
                $rootScope.onUserTokenError();
                break;

                case "11.3.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };

  // "Edit comment" button handler
  $scope.action.onEditComment = function(comment) {
    $scope.new.comment.title = comment.title;
    $scope.new.comment.message = comment.message;

    var modal_editMessage = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, windowClass: "submodal", templateUrl: "modal_editComment.html", controller: "modal_editComment" });
    modal_editMessage.result.then(
      function onModalConfirm() {
        $http.put($rootScope.api.url + "/timeline/comment/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id),
        { headers: { "Authorization": $rootScope.user.token }},
        { data: { token: $rootScope.user.token, messageId: comment.id, title: $scope.new.comment.title, message: $scope.new.comment.message }}).then(
          function onPutCommentSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else {
              Notification.success({ title: "Timeline", message: "Comment updated.", delay: 2000 });
              $scope.active.comment.title = $scope.new.comment.title;
              $scope.active.comment.message = $scope.new.comment.message;
            }
            _getMessageComments($scope.active.message);
          },
          function onPutCommentFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.3.3":
                $rootScope.onUserTokenError();
                break;

                case "11.3.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };



  /* ==================== DELETE OBJECT (DELETE MESSAGE/COMMENT) ==================== */

  // "Delete message" button handler
  $scope.action.onDeleteMessage = function(message_id) {
    var modal_deleteMessage = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "modal_deleteMessage.html", controller: "modal_deleteMessage" });
    modal_deleteMessage.result.then(
      function onModalConfirm(data) {
        $http.delete($rootScope.api.url + "/timeline/message/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id) + "/" + message_id,
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function onDeleteMessageSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else
              Notification.success({ title: "Timeline", message: "Message successfully deleted.", delay: 2000 });
            if ($scope.timeline.team.active)
              $scope.timeline.team.messages = _getTimelineMessages($scope.timeline.team);
            else
              $scope.timeline.customer.messages = _getTimelineMessages($scope.timeline.customer);       
          $scope.active.modal.close();
          },
          function onDeleteMessageFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.6.3":
                $rootScope.onUserTokenError();
                break;

                case "11.6.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };

  // "Delete comment" button handler
  $scope.action.onDeleteComment = function(comment_id) {
    var modal_deleteComment = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "modal_deleteComment.html", controller: "modal_deleteComment" });
    modal_deleteComment.result.then(
      function onModalConfirm(data) {
        $http.delete($rootScope.api.url + "/timeline/comment/" + ($scope.timeline.team.active ? $scope.timeline.team.id : $scope.timeline.customer.id) + "/" + comment_id,
          { headers: { "Authorization": $rootScope.user.token }}).then(
          function onDeleteCommentSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.11.1")
              Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else
              Notification.success({ title: "Timeline", message: "Comment successfully deleted.", delay: 2000 });
            _getMessageComments($scope.active.message);    
          },
          function onDeleteCommentFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "11.6.3":
                $rootScope.onUserTokenError();
                break;

                case "11.6.9":
                Notification.error({ title: "Timeline", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Timeline", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };

}]);



/**
* Controller definition (from view)
* MESSAGE VIEW AND COMMENTS => view message, view comments, new comment form.
*
*/
app.controller("modal_openMessage", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_closeMessage = function() { $uibModalInstance.close(); };
}]);



/**
* Controller definition (from view)
* MESSAGE CREATION => new message form.
*
*/
app.controller("modal_createNewMessage", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, content: false };

  $scope.modal_confirmMessageCreation = function() {
    $scope.error.title = ($scope.new.message.title && $scope.new.message.title.length ? false : true);
    $scope.error.content = ($scope.new.message.message && $scope.new.message.message.length ? false : true);
    if (!$scope.error.title && !$scope.error.content)
      $uibModalInstance.close();
  };
  $scope.modal_cancelMessageCreation = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* COMMENT CREATION => new comment form.
*
*/
app.controller("modal_createNewComment", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, content: false };

  $scope.modal_confirmCommentCreation = function() {
    $scope.error.title = ($scope.new.comment.title && $scope.new.comment.title.length ? false : true);
    $scope.error.content = ($scope.new.comment.message && $scope.new.comment.message.length ? false : true);
    if (!$scope.error.title && !$scope.error.content)
      $uibModalInstance.close();
  };
  $scope.modal_cancelCommentCreation = function() { $uibModalInstance.dismiss(); };
}]);


/**
* Controller definition (from view)
* MESSAGE EDITION => edit message form.
*
*/
app.controller("modal_editMessage", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, content: false };

  $scope.modal_confirmMessageEdition = function() {
    $scope.error.title = ($scope.new.message.title && $scope.new.message.title.length ? false : true);
    $scope.error.content = ($scope.new.message.message && $scope.new.message.message.length ? false : true);
    if (!$scope.error.title && !$scope.error.content)
      $uibModalInstance.close();
  };
  $scope.modal_cancelMessageEdition = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* COMMENT EDITION => edit comment form.
*
*/
app.controller("modal_editComment", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, content: false };

  $scope.modal_confirmCommentEdition = function() {
    $scope.error.title = ($scope.new.comment.title && $scope.new.comment.title.length ? false : true);
    $scope.error.content = ($scope.new.comment.message && $scope.new.comment.message.length ? false : true);
    if (!$scope.error.title && !$scope.error.content)
      $uibModalInstance.close();
  };
  $scope.modal_cancelCommentEdition = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* MESSAGE DELETION => confirmation prompt.
*
*/
app.controller("modal_deleteMessage", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_confirmMessageDeletion = function() { $uibModalInstance.close(); };
  $scope.modal_cancelMessageDeletion = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* COMMENT DELETION => confirmation prompt.
*
*/
app.controller("modal_deleteComment", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_confirmCommentDeletion = function() { $uibModalInstance.close(); };
  $scope.modal_cancelCommentDeletion = function() { $uibModalInstance.dismiss(); };
}]);