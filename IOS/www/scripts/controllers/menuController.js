/*
    Summary: Menu Controller
*/

angular.module('GrappBox.controllers')

.controller('MenuCtrl', function ($scope, $rootScope, $state, Logout) {
    $scope.userDatas = $rootScope.userDatas;

    $scope.logout = function () {
        Logout.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Deconnexion successful !');
                $state.go('auth');
            })
            .catch(function (error) {
                console.error('Deconnexion failed ! Reason: ' + error);
            })
    }
})