/*
    Summary: Menu Controller
*/

angular.module('GrappBox.controllers')

.controller('MenuCtrl', function ($scope, $rootScope) {
    $scope.userDatas = $rootScope.userDatas;
})