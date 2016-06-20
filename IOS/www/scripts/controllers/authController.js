/*
    Summary: Auth page controller
*/

angular.module('GrappBox.controllers')

.controller('AuthCtrl', function ($scope, $rootScope, $state, $ionicHistory, Toast, Auth) {
    $scope.user = {};

    $scope.$on('$ionicView.enter', function () {
        $ionicHistory.clearCache();
    });

    $scope.user.email = "pierre.hofman@epitech.eu";
    $scope.user.password = "hofman_p";
    $scope.login = function () {
        $rootScope.showLoading();
        Auth.Login().save({
            data: {
                login: $scope.user.email,
                password: $scope.user.password
            }
        }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                Toast.show("Connected");
                console.log(data);
                $rootScope.userDatas = data.data;
                $state.go('app.projects');
            })
            .catch(function (error) {
                console.error('Connection failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Connection failed");
                console.error(error);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})