/*
    Summary: Task controller
*/

angular.module('GrappBox.controllers')

.controller('TaskCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Tasks) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });

    $scope.projectId = $stateParams.projectId;
    $scope.taskId = $stateParams.taskId;

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetTask();
        console.log("View refreshed !");
    }

    /*
    ** Get task info
    ** Method: GET
    */
    $scope.task = {};
    $scope.GetTask = function () {
        //$rootScope.showLoading();
        Tasks.Get().get({
            taskId: $scope.taskId
        }).$promise
            .then(function (data) {
                console.log('Get task successful !');
                $scope.task = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get task failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Get task failed");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTask();
})
