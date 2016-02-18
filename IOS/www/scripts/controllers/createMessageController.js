/*
    Create message on timeline controller
*/

angular.module('GrappBox.controllers')

.controller('CreateMessageCtrl', function ($scope, $rootScope, $state, $stateParams,
    PostMessage) {

    $scope.timelineId = $stateParams.timelineId;
    console.log($scope.timelineId);

    /*
    ** Post message
    ** Method: POST
    */
    $scope.message = {};
    $scope.PostMessage = function () {
        $rootScope.showLoading();
        PostMessage.save({
            data: {
                id: $scope.timelineId,
                token: $rootScope.userDatas.token,
                title: $scope.message.title,
                message: $scope.message.message
            }
        }).$promise
            .then(function (data) {
                console.log('Post message successful !');
                $state.go('app.timelines', { projectId: $stateParams.projectId }, { reload: true });
            })
            .catch(function (error) {
                console.error('Post message failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})