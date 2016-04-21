/*
    Summary:User controller
*/

angular.module('GrappBox.controllers')

.controller('UserCtrl', function ($scope, $rootScope, $state, $stateParams, Roles, Users) {
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
        $rootScope.showLoading();
        console.log("userId: " + $stateParams.userId);
        Users.UserInfo().get({
            token: $rootScope.userDatas.token,
            userId: $stateParams.userId
        }).$promise
            .then(function (data) {
                console.log('Get user info successful !');
                $scope.userInfo = data.data;
            })
            .catch(function (error) {
                console.error('Get user info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetUserInfo();

    /*
    ** Get member roles
    ** Method: GET
    */
    $scope.memberRoles = {};
    $scope.GetMemberRoles = function () {
        $rootScope.showLoading();
        Roles.MemberRoles().get({
            token: $rootScope.userDatas.token,
            userId: $stateParams.userId
        }).$promise
            .then(function (data) {
                console.log('Get member roles successful !');
                $scope.memberRoles = data.data.array;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Get member roles failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetMemberRoles();
})