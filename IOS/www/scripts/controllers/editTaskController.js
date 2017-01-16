/*
Edit event on calendar controller
*/

angular.module('GrappBox.controllers')

.controller('EditTaskCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, $ionicPopup, moment, Toast, Projects, Tasks) {

  $scope.$on('$ionicView.beforeEnter', function () {
    $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
  });


  //Refresher
  $scope.doRefresh = function () {
    $scope.GetTask();
    console.log("View refreshed !");
  }

  $scope.projectId = $stateParams.projectId;
  $scope.taskId = $stateParams.taskId;

  $scope.objectType = {
    selected: "regular",
  };

  $scope.userSelected = [];

  // Add a user and its charge to currentUsers array
  $scope.addUserToSelection = function(userSelected, chargeSelected) {
    console.log("userSelected = " + userSelected + "| chargeSelected = " + chargeSelected);
    if (userSelected == undefined || chargeSelected == undefined) {
      console.error("No user or no charge selected");
      return ;
    }
    var index = -1;
    for (var i = 0; i < $scope.currentUsers.length && index < 0; i++) {
      if ($scope.currentUsers[i].id == userSelected.id)
        index = i;
    }
    if (index >= 0) {
      Toast.show("User already added");
      return ;
    }
    $scope.currentUsers.push({id: userSelected.id, firstname: userSelected.firstname, lastname: userSelected.lastname, percent: chargeSelected});
    console.log($scope.currentUsers);
    return ;
  }

  // Delete a user and its charge from currentUsers array
  $scope.deleteUserFromSelection = function(userId) {
    for(var i = 0; $scope.currentUsers.length; i++){
      if ($scope.currentUsers[i].id === userId)
        $scope.currentUsers.splice(i, 1);
    }
  }

  /*
  ** Get task info
  ** Method: GET
  */

  $scope.baseSelectedUsers = [];
  $scope.currentUsers = [];
  $scope.task = {};
  $scope.GetTask = function () {
    //$rootScope.showLoading();
    Tasks.Get().get({
      taskId: $scope.taskId
    }).$promise
    .then(function (data) {
      console.log('Get task successful !');
      $scope.task = data.data;
      $scope.baseSelectedUsers = data.data.users;
      $scope.task.started_at = moment($scope.task.started_at).toDate();
      $scope.task.due_date = moment($scope.task.due_date).toDate();
      $scope.objectType.selected = $scope.task.is_milestone == true ? "milestone" : $scope.task.is_container == true ? "container" : "regular"
      console.log(data.data);
      console.log($scope.task.users[0].id);
      for (var i = 0; i < $scope.task.users.length; i++){
        $scope.currentUsers.push($scope.task.users[i]);
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
  $scope.GetTask();

  /*
  ** Edit task
  ** Method: PUT
  */
  $scope.usersToAdd = [];
  $scope.usersToRemove = [];
  $scope.EditTask = function () {

    $scope.usersToRemove = $scope.baseSelectedUsers.filter(function(current){
      return $scope.currentUsers.filter(function(current_b){
        return current_b.id == current.id && current_b.percent == current.percent
      }).length == 0
    });

    $scope.usersToAdd = $scope.currentUsers.filter(function(current){
      return $scope.baseSelectedUsers.filter(function(current_a){
        return current_a.id == current.id && current_a.percent == current.percent
      }).length == 0
    });
    console.log($scope.usersToRemove);
    console.log($scope.usersToAdd);

    Tasks.Edit().update({
      taskId: $scope.task.id,
      data: {
        projectId: $stateParams.projectId,
        title: $scope.task.title,
        description: $scope.task.description,
        started_at: $scope.task.started_at,
        due_date: $scope.task.due_date,
        is_milestone: $scope.objectType.selected == "milestone" ? true : false,
        is_container: $scope.objectType.selected == "container" ? true : false,
        usersAdd: $scope.usersToAdd ? $scope.usersToAdd : [],
        usersRemove: $scope.usersToRemove ? $scope.usersToRemove : []
      }
    }).$promise
    .then(function (data) {
      console.log('Edit task successful !');
      console.log(data);
      Toast.show("Task edited");
    })
    .catch(function (error) {
      Toast.show("Edit task error");
      console.error('Task edition failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
    })
    .finally(function () {
      $rootScope.hideLoading();
    })
  }

  /*
  ** Get users on project
  ** Method: GET
  */
  $scope.usersList = {};
  $scope.GetUsersOnProject = function () {
    Projects.Users().get({
      id: $stateParams.projectId
    }).$promise
    .then(function (data) {
      $scope.usersList = data.data.array;
      // We stock entire name so we can use it in <select>
      angular.forEach($scope.usersList, function(user){
        user["name"] = user.firstname + " " + user.lastname;
      });
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

})
