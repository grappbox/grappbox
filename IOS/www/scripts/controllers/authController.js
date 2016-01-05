/*
    Summary: Auth page controller
*/

angular.module('GrappBox.controllers')

.controller('AuthCtrl', function ($scope, $rootScope, $state, Login, Logout) {
    $scope.user = {};

    $scope.login = function () {
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
    }

    $scope.loginWithToken = function () {
        Login.save({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                $rootScope.userDatas = data.user;
                $state.go('app.dashboard');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error);
            })
    }
})