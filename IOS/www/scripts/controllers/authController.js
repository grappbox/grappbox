/*
    Summary: Auth page controller
*/

angular.module('GrappBox.controllers')

.controller('AuthCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $ionicHistory, $cordovaDevice, $http, Toast, Auth) {

    $scope.device = {};

    $ionicPlatform.ready(function() {
      if (document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1) {
        $scope.device = $cordovaDevice.getDevice();
        console.log($scope.device);
      }
    });

    $scope.user = {};

    $scope.$on('$ionicView.enter', function () {
        $ionicHistory.clearCache();
    });

    /*
    ** TO REMOVE FOR PROD
    */
    $scope.user.email = "pierre.hofman@epitech.eu";
    $scope.user.password = "tan_p";
    $scope.device.model = "HTC_One_M8";
    $scope.device.platform = "ios";
    $scope.device.uuid = "424242";

    $scope.login = function () {
        $rootScope.showLoading();
        Auth.Login().save({
            data: {
                login: $scope.user.email,
                password: $scope.user.password,
                mac: $scope.device.uuid,
                flag: $scope.device.platform,
                device_name: $scope.device.model
            }
        }).$promise
            .then(function (data) {
                console.log('Connexion successful !');
                console.log(data);
                $rootScope.userDatas = data.data;
                $http.defaults.headers.common.Authorization = $rootScope.userDatas.token;
                $rootScope.hideLoading();
                Toast.show("Connected");
                $state.go('app.projects');
            })
            .catch(function (error) {
                console.error('Connection failed ! Reason: ' + error.status + ' ' + error.statusText);
                $rootScope.hideLoading();
                Toast.show("Bad login or password");
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})
