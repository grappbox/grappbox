/*
    Summary: Profile controller
*/

angular.module('GrappBox.controllers')

.controller('TestCtrl', function ($scope, $rootScope, $state, $stateParams) {
    $scope.projectId = $stateParams.projectId;
})