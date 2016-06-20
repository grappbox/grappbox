/*
    edit tag on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('EditTagCtrl', function ($scope, $rootScope, $state, $stateParams, Toast, Bugtracker) {

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
            token: $rootScope.userDatas.token,
            tagId: $scope.tagId
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
            data: {
                token: $rootScope.userDatas.token,
                tagId: $scope.tagId,
                name: $scope.tagInfo.name
            }
        }).$promise
            .then(function (data) {
                console.log('Update tag successful !');
                $scope.project = data.data;
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.tags', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                console.error('Update tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

})