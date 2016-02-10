/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')
.controller('TimelinesCtrl', function ($ionicPlatform, $scope, $cordovaDevice) {
    $ionicPlatform.ready(function () {
        $scope.device = $cordovaDevice.getDevice();
        $scope.cordova = $cordovaDevice.getCordova();
        $scope.model = $cordovaDevice.getModel();
        $scope.platform = $cordovaDevice.getPlatform();
        $scope.uuid = $cordovaDevice.getUUID();
        $scope.version = $cordovaDevice.getVersion();

        $scope.changeOriantationLandspace = function () {
            screen.lockOrientation('landscape');
        }

        $scope.changeOriantationPortrait = function () {
            screen.lockOrientation('portrait');
        }
    });
})