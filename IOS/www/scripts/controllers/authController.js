/*
    Summary: Auth page controller
*/

angular.module('GrappBox.controllers')

.controller('AuthCtrl', function ($scope, $rootScope, $state, Login, $ionicHistory) {
    $scope.user = {};

    $scope.$on('$ionicView.enter', function () {
        $ionicHistory.clearCache();
    });

    $scope.user.email = "pierre.hofman@epitech.eu";
    $scope.user.password = "hofman_p";
    $scope.login = function () {
        $rootScope.showLoading();
        Login.save({
            data: {
                login: $scope.user.email,
                password: $scope.user.password
            }
        }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                console.log(data);
                $rootScope.userDatas = data.data;
                $state.go('app.dashboard');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})