/*
    Create message on timeline controller
*/

angular.module('GrappBox.controllers')

.controller('CreateMessageCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Timeline) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.timeline;
    });

    $scope.timelineId = $stateParams.timelineId;
    console.log($scope.timelineId);

    /*
    ** Post message
    ** Method: POST
    */
    $scope.message = {};
    $scope.PostMessage = function () {
        //$rootScope.showLoading();
        Timeline.PostMessage().save({
            id: $scope.timelineId,
            data: {
                title: $scope.message.title,
                message: $scope.message.message
            }
        }).$promise
            .then(function (data) {
                console.log('Post message successful !');
                Toast.show("Message created");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.timelines', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                console.error('Post message failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Message error");
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})