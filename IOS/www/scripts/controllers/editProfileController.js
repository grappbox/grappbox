/*
    Summary: Edit single project controller
*/

angular.module('GrappBox.controllers')

.controller('EditProfileCtrl', function ($scope, $rootScope, $state, Toast, Users) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProfileInfo();
        console.log("View refreshed !");
    }

    /*
    ** Get connected user information
    ** Method: GET
    */
    $scope.profileInfo = {};
    $scope.GetProfileInfo = function () {
        //$rootScope.showLoading();
        Users.ProfileInfo().get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get profile info successful !');
                $scope.profileInfo = data.data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get profile info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                //$scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetProfileInfo();

    $scope.EditProfile = function () {
        //$rootScope.showLoading();
        Users.EditProfile().update({
            data: {
                firstname: $scope.profileInfo.first_name ? $scope.profileInfo.first_name : "",
                lastname: $scope.profileInfo.last_name ? $scope.profileInfo.last_name : "",
                birthday: $scope.profileInfo.birthday ? $scope.profileInfo.birthday : "",
                avatar: $scope.profileInfo.avatar ? $scope.profileInfo.avatar : "",
                email: $scope.profileInfo.email ? $scope.profileInfo.email : "",
                oldPassword: $scope.profileInfo.oldPassword ? $scope.profileInfo.oldPassword && $scope.profileInfo.password : "",
                password: $scope.profileInfo.password ? $scope.profileInfo.password && $scope.profileInfo.oldPassword : "",
                phone: $scope.profileInfo.phone ? $scope.profileInfo.phone : "",
                country: $scope.profileInfo.country ? $scope.profileInfo.country : "",
                linkedin: $scope.profileInfo.linkedIn ? $scope.profileInfo.linkedIn : "",
                viadeo: $scope.profileInfo.viadeo ? $scope.profileInfo.viadeo : "",
                twitter: $scope.profileInfo.twitter ? $scope.profileInfo.twitter : ""
            }
        }).$promise
            .then(function (data) {
                console.log('Edit profile successful !');
                Toast.show("Profile edited");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.profile');
                });
            })
            .catch(function (error) {
                Toast.show("Profile error");
                console.error('Edit profile failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})