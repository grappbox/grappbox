/*
    Summary: Edit single project controller
*/

angular.module('GrappBox.controllers')

.controller('EditProfileCtrl', function ($scope, $rootScope, $state, $ionicHistory, Toast, Users) {

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
                $scope.profileInfo.birthday = new Date(data.data.birthday);
                console.log(data.data);
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

    /*
    ** Get user avatar
    ** Method: GET
    */
    $scope.userAvatar = {};
    $scope.GetUserAvatar = function () {
        //$rootScope.showLoading();
        Users.Avatar().get({
            token: $rootScope.userDatas.token,
            userId: $rootScope.userDatas.id
        }).$promise
            .then(function (data) {
                console.log('Get user connected avatar successful !');
                $scope.userAvatar.avatar = data.data.avatar;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get user connected avatar failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUserAvatar();

    $scope.EditProfile = function () {
        $rootScope.showLoading();
        Users.EditProfile().update({
            token: $rootScope.userDatas.token,
            data: {
                firstname: $scope.profileInfo.firstname ? $scope.profileInfo.firstname : "",
                lastname: $scope.profileInfo.lastname ? $scope.profileInfo.lastname : "",
                birthday: $scope.profileInfo.birthday ? $scope.profileInfo.birthday : "",
                avatar: $scope.userAvatar.avatar.base64 ? $scope.userAvatar.avatar.base64 : "",
                email: $scope.profileInfo.email ? $scope.profileInfo.email : "",
                oldPassword: $scope.profileInfo.oldPassword && $scope.profileInfo.password ? $scope.profileInfo.oldPassword : "",
                password: $scope.profileInfo.password && $scope.profileInfo.oldPassword ? $scope.profileInfo.password : "",
                phone: $scope.profileInfo.phone ? $scope.profileInfo.phone : "",
                country: $scope.profileInfo.country ? $scope.profileInfo.country : "",
                linkedin: $scope.profileInfo.linkedIn ? $scope.profileInfo.linkedIn : "",
                viadeo: $scope.profileInfo.viadeo ? $scope.profileInfo.viadeo : "",
                twitter: $scope.profileInfo.twitter ? $scope.profileInfo.twitter : ""
            }
        }).$promise
            .then(function (data) {
                console.log('Edit profile successful !');
                $rootScope.hideLoading();
                if ($scope.userAvatar.avatar) {
                    $scope.$emit('reloadAvatar');
                }
                Toast.show("Profile edited");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.profile');
                });
            })
            .catch(function (error) {
                $rootScope.hideLoading();
                Toast.show("Profile error");
                console.error('Edit profile failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})