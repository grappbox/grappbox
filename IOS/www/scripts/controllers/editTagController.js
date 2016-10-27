/*
    edit tag on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('EditTagCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Bugtracker) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.bugtracker;
    });

    $scope.doRefresh = function () {
        $scope.GetTagInfo();
        console.log("View refreshed !");
    }

    $scope.tagId = $stateParams.tagId;

    /*
    ** Get all tags
    ** Method: GET
    */
    $scope.tagInfo = {};
    $scope.GetTagInfo = function () {
        $rootScope.showLoading();
        Bugtracker.GetTagInfo().get({
            id: $scope.tagId
        }).$promise
            .then(function (data) {
                console.log('Get tag info successful !');
                Toast.show("Tag edited");
                $scope.tagInfo = data.data;
            })
            .catch(function (error) {
                console.error('Get tag info failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTagInfo();

    /*
    ** Update Tag
    ** Method: PUT
    */
    $scope.updateTagData = {};
    $scope.UpdateTag = function () {
        //$rootScope.showLoading();
        Bugtracker.UpdateTag().update({
            id: $scope.tagId,
            data: {
                name: $scope.tagInfo.name,
                color: "9E58DC"
            }
        }).$promise
            .then(function (data) {
                console.log('Update tag successful !');
                $scope.project = data.data;
                Toast.show("Tag updated");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.tags', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                console.error('Update tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                Toast.show("Tag edition error");
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

})