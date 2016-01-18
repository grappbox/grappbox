/*
    Summary: Edit single project controller
*/

angular.module('GrappBox.controllers')

.controller('EditProfileCtrl', function ($scope, $rootScope, $state, GetProfileInfo, EditProfile) {

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
        GetProfileInfo.get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get profile info successful !');
                $scope.profileInfo = data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get profile info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetProfileInfo();

    $scope.EditProfile = function () {
        /*EditProfile.update({
            token: $rootScope.userDatas.token,
            first_name: $scope.profileInfo.first_name,
            last_name: $scope.profileInfo.last_name,
            birthday: $scope.profileInfo.birthday,
            avatar: $scope.profileInfo.avatar,
            email: $scope.profileInfo.email,
            password: $scope.profileInfo.password,
            phone: $scope.profileInfo.phone,
            country: $scope.profileInfo.country,
            linkedin: $scope.profileInfo.linkedIn,
            viadeo: $scope.profileInfo.viadeo,
            twitter: $scope.profileInfo.twitter,
        }).$promise
            .then(function (data) {
                console.log('Edit profile successful !');
                $state.go('app.profile');
            })
            .catch(function (error) {
                console.error('Edit profile failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })*/
    }    
})