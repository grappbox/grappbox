/*
    Summary:User controller
*/

angular.module('GrappBox.controllers')

.controller('UserCtrl', function ($scope, $rootScope, $state, $stateParams, GetUserInfo) {
    //Refresher
    $scope.doRefresh = function () {
        $scope.GetUserInfo();
        console.log("View refreshed !");
    }

    /*
    ** Get connected user information
    ** Method: GET
    */
    $scope.userInfo = {};
    $scope.GetUserInfo = function () {
        GetUserInfo.get({
            token: $rootScope.userDatas.token,
            userId: $stateParams.userId
        }).$promise
            .then(function (data) {
                console.log('Get user info successful !');
                $scope.userInfo = data;
            })
            .catch(function (error) {
                console.error('Get user info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetUserInfo();
})