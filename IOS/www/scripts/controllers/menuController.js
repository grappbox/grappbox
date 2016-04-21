/*
    Summary: Menu Controller
*/

angular.module('GrappBox.controllers')

.controller('MenuCtrl', function ($scope, $rootScope, $state, $ionicLoading, $ionicHistory, Auth) {
    $scope.userDatas = $rootScope.userDatas;

    $scope.logout = function () {
        Auth.Logout().get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Deconnexion successful !');
                $ionicLoading.show({ template: 'Logging out....' });

                $ionicHistory.clearCache().then(function () {
                    $ionicHistory.clearHistory();
                    $ionicHistory.nextViewOptions({ disableBack: true, historyRoot: true });
                    $ionicLoading.hide();
                    $state.go('auth');
                });
            })
            .catch(function (error) {
                console.error('Deconnexion failed ! Reason: ' + error);
            })
    }
})