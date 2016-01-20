/*
    Summary: Auth page controller
*/

angular.module('GrappBox.controllers')

.controller('AuthCtrl', function ($scope, $rootScope, $state, Login, $ionicHistory) {
    $scope.user = {};

    $scope.$on('$ionicView.enter', function () {
        $ionicHistory.clearCache();
    });

    $scope.login = function () {
        $rootScope.showLoading();
        Login.save({ login: $scope.user.email, password: $scope.user.password }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                console.log(data);
                $rootScope.userDatas = data.user;
                $state.go('app.dashboard');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }

    $scope.loginWithToken = function () {
        $rootScope.showLoading();
        Login.save({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                $rootScope.userDatas = data.user;
                $state.go('app.dashboard');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})