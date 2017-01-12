/*
Summary: Task Controller
*/

angular.module('GrappBox.controllers')

.controller('TasksCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, Toast, Tasks) {

  // Disable back button & set nav color
  $scope.$on('$ionicView.beforeEnter', function (event, viewData) {
    $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    viewData.enableBack = false;
  });

  // Refresh
  $scope.doRefreshTasksList = function () {
    $scope.GetTasksList();
    console.log("View refreshed !");
  }

  $scope.projectId = $stateParams.projectId;

  //Get tasks list
  $scope.tasksListData = {};
  $scope.GetTasksList = function () {
    //$rootScope.showLoading();
    Tasks.List().get({
      projectId: $scope.projectId
    }).$promise
    .then(function (data) {
      console.log('Get tasks list successful !');
      console.log(data.data.array);
      $scope.tasksListData = data.data.array;
      if (data.data.array.length == 0)
        $scope.noTask = "You don't have any task.";
      else {
        $scope.noTask = false;
      }
    })
    .catch(function (error) {
      console.error('Get tasks list failed ! Reason: ' + error.status + ' ' + error.statusText);
      console.error(error);
    })
    .finally(function () {
      $scope.$broadcast('scroll.refreshComplete');
      //$rootScope.hideLoading();
    })
  }
  $scope.GetTasksList();

  /*
  ** Delete task
  ** Method: DELETE
  */
  $scope.deteleTaskData = {};
  $scope.DeleteTask = function (taskId) {
    //$rootScope.showLoading();
    Tasks.Delete().delete({
      taskId: taskId
    }).$promise
    .then(function (data) {
      console.log('Delete task successful !');
      $scope.deteleTaskData = data.data;
      console.log(data);
      //$rootScope.hideLoading();
      $scope.GetTasksList();
    })
    .catch(function (error) {
      console.error('Delete task failed ! Reason: ' + error.status + ' ' + error.statusText);
      Toast.show("Delete task failed");
      console.error(error);
    })
    .finally(function () {
      $scope.$broadcast('scroll.refreshComplete');
      //$rootScope.hideLoading();
    })
  }
})
