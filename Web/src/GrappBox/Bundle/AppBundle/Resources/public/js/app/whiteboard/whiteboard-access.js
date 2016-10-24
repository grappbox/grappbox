/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =========================================================== */
/* ==================== WHITEBOARD ACCESS ==================== */
/* =========================================================== */

// Routine definition
// APP whiteboard page access
var isWhiteboardAccessible = function($rootScope, $q, $http, $route, $location, Notification) {
  var deferred = $q.defer();

  $http.get($rootScope.api.url + "/whiteboards/" + $route.current.params.project_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
  	function onGetWhiteboardListSuccess(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "1.10.1":
          var whiteboardList = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
          var whiteboard_id = $route.current.params.id;
          var isWhiteboardKnown = false;

          angular.forEach(whiteboardList, function(value, key) {
          	if (value.id == whiteboard_id)
          		isWhiteboardKnown = true;
          });

          if (isWhiteboardKnown)
          	deferred.resolve();
          else {
          	deferred.reject();
          	$location.path("whiteboard/" + $route.current.params.project_id);
          	Notification.warning({ title: "Whiteboard", message: "Whiteboard not found.", delay: 4500 });
          }
          break;

          case "1.10.3":
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          Notification.warning({ title: "Whiteboard", message: "Whiteboard not found.", delay: 4500 });
          break;

          default:
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          Notification.warning({ title: "Whiteboard", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
          break;
        }
      }
      else {
        deferred.reject();
        $location.path("whiteboard/" + $route.current.params.project_id);
        Notification.warning({ title: "Whiteboard", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
	    }
    },
    function onGetWhiteboardListFail(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "10.1.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "10.1.9":
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          Notification.warning({ title: "Whiteboard", message: "You don't have sufficient rights to perform this operation.", delay: 4500 });
          break;

          default:
          deferred.reject();
          $location.path("whiteboard/" + $route.current.params.project_id);
          Notification.warning({ title: "GrappBox", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
          break;
        }
      }
      else {
        deferred.reject();
        $location.path("whiteboard/" + $route.current.params.project_id);
        Notification.warning({ title: "GrappBox", message: "Someting is wrong with GrappBox. Please try again.", delay: 4500 });
      }
    });

    return deferred.promise;
};

// "isWhiteboardAccessible" routine injection
isWhiteboardAccessible["$inject"] = ["$rootScope", "$q", "$http", "$route", "$location", "Notification"];