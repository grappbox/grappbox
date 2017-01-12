/*
Edit event on calendar controller
*/

angular.module('GrappBox.controllers')

.controller('EditEventCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, $ionicPopup, moment, Toast, Projects, Event) {

  $scope.$on('$ionicView.beforeEnter', function () {
    $rootScope.viewColor = $rootScope.GBNavColors.calendar;
  });


  //Refresher
  $scope.doRefresh = function () {
    $scope.GetEvents();
    console.log("View refreshed !");
  }

  $scope.projectId = $stateParams.projectId;
  $scope.eventId = $stateParams.eventId;

  //Assign user list manually to scope because it seems that Ionic bug with ng-model and <select>
  $scope.updateUserList = function (selectedUsers) {
    $scope.selectedUsers = selectedUsers;
    console.log("User List is now:");
    console.log($scope.selectedUsers);
  }

  /*
  ** Get event info
  ** Method: GET
  */
  $scope.selectedUsers = [];
  $scope.baseSelectedUsers = [];
  $scope.event = {};
  $scope.GetEvent = function () {
    //$rootScope.showLoading();
    Event.Get().get({
      id: $scope.eventId
    }).$promise
    .then(function (data) {
      console.log('Get event successful !');
      $scope.event = data.data;
      console.log(data.data);
      // Transform into moment date understood by datetime-local in html
      $scope.event.beginDate = moment($scope.event.beginDate).toDate();
      $scope.event.endDate = moment($scope.event.endDate).toDate();
      if ($scope.projectId) {
        // add all selected users in $scope.selectedUsers, convert it to string for ng-model
        console.log($scope.event.users[0].id);
        for (var i = 0; i < $scope.event.users.length; i++){
          $scope.selectedUsers.push($scope.event.users[i].id.toString());
        }
        // copy all selected users in a basic array, so we can do some operations on it for "toAdd" and "toRemove"
        $scope.baseSelectedUsers = $scope.selectedUsers;
        console.log($scope.selectedUsers);
        console.log($scope.baseSelectedUsers);
      }
    })
    .catch(function (error) {
      console.error('Get event failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
    })
    .finally(function () {
      $scope.$broadcast('scroll.refreshComplete');
      //$rootScope.hideLoading();
    })
  }
  $scope.GetEvent();

  /*
  var A = [1, 2, 3, 4, 5];
  var B = [1, 4, 6, 7];

  var diff1 = A.filter(function(x) { return B.indexOf(x) < 0 });
  var diff2 = B.filter(function(x) { return A.indexOf(x) < 0 });
  console.log(diff1); // 2,3,5
  console.log(diff2); // 6,7
  */

  /*
  ** Edit event
  ** Method: PUT
  */
  $scope.toAddUsers = [];
  $scope.toRemoveUsers = [];
  $scope.EditEvent = function () {
    //$rootScope.showLoading();
    if ($scope.projectId) {
      // If there are users from baseSelectedUsers in selectedUsers...
      $scope.toRemoveUsers = $scope.baseSelectedUsers.filter(function(x) {
        return $scope.selectedUsers.indexOf(x) < 0
      });
      // If there are users from selectedUsers in baseSelectedUsers...
      $scope.toAddUsers = $scope.selectedUsers.filter(function(x) {
        return $scope.baseSelectedUsers.indexOf(x) < 0
      });
    }
    Event.Edit().update({
      id: $scope.event.id,
      data: ($scope.projectId ?
        {
          projectId: $stateParams.projectId,
          title: $scope.event.title,
          description: $scope.event.description,
          begin: $scope.event.beginDate,
          end: $scope.event.endDate,
          toAddUsers: $scope.toAddUsers ? $scope.toAddUsers : [],
          toRemoveUsers: $scope.toRemoveUsers ? $scope.toRemoveUsers : []
        } :
        {
          title: $scope.event.title,
          description: $scope.event.description,
          begin: $scope.event.beginDate,
          end: $scope.event.endDate
        }
      )
    }).$promise
    .then(function (data) {
      console.log('Edit event successful !');
      console.log(data);
      //$rootScope.hideLoading();
      Toast.show("Event edited");
    })
    .catch(function (error) {
      //$rootScope.hideLoading();
      Toast.show("Edit event error");
      console.error('Event edition failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
    })
    .finally(function () {
      $rootScope.hideLoading();
    })
  }

  /*
  ** Delete event
  ** Method: DELETE
  */
  $scope.deteleEventData = {};
  $scope.DeleteEvent = function () {
    //$rootScope.showLoading();
    Event.Delete().delete({
      id: $scope.eventId
    }).$promise
    .then(function (data) {
      console.log('Delete event successful !');
      $scope.deteleEventData = data.data;
      console.log(data.data);
      //$rootScope.hideLoading();
      $ionicHistory.clearCache().then(function () {
        $state.go('app.calendar', { projectId: $stateParams.projectId });
      });
    })
    .catch(function (error) {
      console.error('Delete event failed ! Reason: ' + error.status + ' ' + error.statusText);
      Toast.show("Delete event failed");
      console.error(error);
    })
    .finally(function () {
      $scope.$broadcast('scroll.refreshComplete');
      //$rootScope.hideLoading();
    })
  }

  /*
  ** Get users on project
  ** Method: GET
  */
  $scope.userList = {};
  $scope.GetUsersOnProject = function () {
    Projects.Users().get({
      id: $stateParams.projectId
    }).$promise
    .then(function (data) {
      $scope.userList = data.data.array;
      console.log('Get project members successful !');
      console.log(data.data.array);
    })
    .catch(function (error) {
      console.error('Get project members failed ! Reason: ' + error.status + ' ' + error.statusText);
      Toast.show("Get project members failed");
      console.error(error);
      //$rootScope.hideLoading();
    })
    .finally(function () {
      $scope.$broadcast('scroll.refreshComplete');
    })
  }
  if ($scope.projectId)
    $scope.GetUsersOnProject();


  /*
  ** POPUPS
  */

  // Remove confirm popup for deleting event
  $scope.PopupDeleteEvent = function () {
    var confirmPopup = $ionicPopup.confirm({
      title: 'Delete event',
      template: 'Are you sure you want to delete this event ? '
    })
    .then(function (res) {
      if (res) {
        $scope.DeleteEvent();
        console.log("Chose to delete event");
      } else {
        console.log("Chose to keep event");
      }
    })
  }


})
