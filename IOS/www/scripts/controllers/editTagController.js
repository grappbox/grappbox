/*
    edit tag on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('EditTagCtrl', function ($scope, $rootScope, $state, $stateParams,
    GetTagInfo, UpdateTag) {

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
        GetTagInfo.get({
            token: $rootScope.userDatas.token,
            tagId: $scope.tagId
        }).$promise
            .then(function (data) {
                console.log('Get tag info successful !');
                $scope.tagInfo = data.data;
            })
            .catch(function (error) {
                console.error('Get tag info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTagInfo();

    /*
    ** Edit ticket
    ** Method: PUT
    */
    $scope.updateTagData = {};
    $scope.UpdateTag = function () {
        $rootScope.showLoading();
        UpdateTag.update({
            data: {
                token: $rootScope.userDatas.token,
                tagId: $scope.tagId,
                name: $scope.tagInfo.name
            }
        }).$promise
            .then(function (data) {
                console.log('Edit ticket successful !');
                $scope.project = data.data;
                $state.go('app.tags', { projectId: $stateParams.projectId });
            })
            .catch(function (error) {
                console.error('Edit ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

})