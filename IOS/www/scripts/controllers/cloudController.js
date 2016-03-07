/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')
.controller('CloudCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, $ionicPopup,
    CloudLS) {

    $scope.projectId = $stateParams.projectId;
    $scope.userConnectedId = $rootScope.userDatas.id;

    //Refresher
    $scope.doRefresh = function () {
        $scope.CloudLS();
        console.log("View refreshed !");
    }

    $scope.path = ",";

    /*
    ** Cloud LS
    ** Method: GET
    */
    $scope.cloudData = {};
    $scope.CloudLS = function () {
        $rootScope.showLoading();
        CloudLS.get({
            token: $rootScope.userDatas.token,
            idProject: $scope.projectId,
            path: $scope.path
        }).$promise
            .then(function (data) {
                console.log('Cloud LS successful !');
                $scope.cloudData = data.data.array;
            })
            .catch(function (error) {
                console.error('Cloud LS failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.CloudLS();
})