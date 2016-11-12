/*
    Summary: Menu Controller
*/

angular.module('GrappBox.controllers')

.controller('MenuCtrl', function ($scope, $rootScope, $state, $ionicLoading, $ionicHistory, Auth, Users) {
    $scope.userDatas = $rootScope.userDatas;

    $scope.logout = function () {
        Auth.Logout().get()
            .$promise
            .then(function (data) {
                console.log('Disconnection successful !');
                $ionicLoading.show({ template: 'Logging out....' });

                $ionicHistory.clearCache().then(function () {
                    $ionicHistory.clearHistory();
                    $ionicHistory.nextViewOptions({ disableBack: true, historyRoot: true });
                    $ionicLoading.hide();
                    $state.go('auth');
                });
            })
            .catch(function (error) {
                console.error('Disconnection failed ! Reason: ');
                console.error(error);
            })
    }
    // used in editProfileController.js
    $scope.$on('reloadAvatar', function (event, args) {
        $scope.GetUserAvatar();
    });

    /*
    ** Get user avatar
    ** Method: GET
    */
    $scope.userAvatar = {};
    $scope.GetUserAvatar = function () {
        //$rootScope.showLoading();
        Users.Avatar().get({
            userId: $rootScope.userDatas.id
        }).$promise
            .then(function (data) {
                console.log('Get user connected avatar successful !');
                $scope.userAvatar.avatar = data.data.avatar;
                //console.log(data.data);
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
})
