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
                $rootScope.userDatas = data.user;
                $state.go('app.projects');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error);
            })
    }

    $scope.loginWithToken = function () {
        Login.save({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                $rootScope.userDatas = data.user;
                $state.go('app.projects');
            })
            .catch(function (error) {
                console.error('Connexion failed ! Reason: ' + error);
            })
    }

    $scope.logout = function () {
        Logout.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Deconnexion successful !');
            })
            .catch(function (error) {
                console.error('Deconnexion failed ! Reason: ' + error);
            })
    }
})