/*
    Summary: Event controller
*/

angular.module('GrappBox.controllers')

.controller('EventCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Event) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.calendar;
    });

    $scope.projectId = $stateParams.projectId;
    $scope.eventId = $stateParams.eventId;

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetEvent();
        console.log("View refreshed !");
    }

    /*
    ** Get event info
    ** Method: GET
    */
    $scope.event = {};
    $scope.GetEvent = function () {
        //$rootScope.showLoading();
        Event.Get().get({
            id: $scope.eventId
        }).$promise
            .then(function (data) {
                console.log('Get event successful !');
                $scope.event = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get event failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Get event failed");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetEvent();
})
