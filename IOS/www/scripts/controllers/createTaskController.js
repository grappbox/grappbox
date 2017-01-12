/*
Create task controller
*/

angular.module('GrappBox.controllers')

.filter('range', function() {
  return function(input, total) {
    total = parseInt(total);
    for (var i = 0; i < total; i++) {
      input.push(i*10);
    }
    return input;
  };
})

.controller('CreateTaskCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Tasks) {

  $scope.$on('$ionicView.beforeEnter', function () {
    $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
  });

  $scope.task = {};
  $scope.userSelected = [];
  $scope.usersToAdd = [];
  $scope.projectId = $stateParams.projectId;

  $scope.objectType = {
    selected: "regular",
  };

  // Add a user and its charge to usersToAdd array
  $scope.addUserToTask = function(userSelected, chargeSelected) {
    if (userSelected == "" || chargeSelected == "") {
      console.error("No user or no charge selected");
      return ;
    }
    var index = -1;
    for (var i = 0; i < $scope.usersToAdd.length && index < 0; i++) {
      if ($scope.usersToAdd[i].id == userSelected.id)
        index = i;
    }
    if (index >= 0) {
      Toast.show("User already added");
      return ;
    }
    $scope.usersToAdd.push({id: userSelected.id, firstname: userSelected.firstname, lastname: userSelected.lastname, percent: chargeSelected});
    console.log($scope.usersToAdd);
    return ;
  }

  // Delete a user and its charge from usersToAdd array
  $scope.deleteFromUserToAdd = function(userId) {
    for(var i = 0; $scope.usersToAdd.length; i++){
      if ($scope.usersToAdd[i].id === userId)
        $scope.usersToAdd.splice(i, 1);
    }
  }

  /*
  ** Create task
  ** Method: POST
  */
  $scope.CreateTask = function () {
    //$rootScope.showLoading();
    console.log($scope.usersToAdd);
    Tasks.Create().save({
      data:
      {
        projectId: $stateParams.projectId,
        title: $scope.task.title,
        description: $scope.task.description,
        started_at: $scope.task.beginDate,
        due_date: $scope.task.endDate,
        is_milestone: $scope.objectType.selected == "milestone" ? true : false,
        is_container: $scope.objectType.selected == "container" ? true : false,
        users: $scope.usersToAdd ? $scope.usersToAdd : []
      }
    }).$promise
    .then(function (data) {
      console.log('Create task successful !');
      console.log(data);
      //$rootScope.hideLoading();
      Toast.show("Task created");
      $ionicHistory.clearCache().then(function () {
        $state.go('app.tasks', { projectId: $stateParams.projectId });
      });
    })
    .catch(function (error) {
      //$rootScope.hideLoading();
      Toast.show("Task creation error");
      console.error('Task creation failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
    })
    .finally(function () {
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
      // We stock entire name so we can use it in <select>
      angular.forEach($scope.userList, function(user){
        user["name"] = user.firstname + " " + user.lastname;
      });
      console.log('Get project members successful !');
      console.log(data.data.array);
    })
    .catch(function (error) {
      console.error('Get project members failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
      $scope.$broadcast('scroll.refreshComplete');
      //$rootScope.hideLoading();
    })
  }
  $scope.GetUsersOnProject();
})
