/*
    Summary:User controller
*/

angular.module('GrappBox.controllers')

.controller('UserCtrl', function ($scope, $rootScope, $state, $stateParams,
    GetUserInfo, GetMemberRoles) {
    //Refresher
    $scope.doRefresh = function () {
        $scope.GetUserInfo();
        $scope.GetMemberRoles();
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

    /*
    ** Get member roles
    ** Method: GET
    */
    $scope.memberRoles = {};
    $scope.GetMemberRoles = function () {
        GetMemberRoles.get({
            token: $rootScope.userDatas.token,
            userId: $stateParams.userId
        }).$promise
            .then(function (data) {
                console.log('Get member roles successful !');
                $scope.memberRoles = data;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Get member roles failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }
    $scope.GetMemberRoles();
})