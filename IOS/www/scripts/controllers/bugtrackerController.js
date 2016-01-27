/*
    Summary: Bugtracker controller
*/

angular.module('GrappBox.controllers')

.controller('BugtrackerCtrl', function ($scope, $rootScope, $state, $stateParams,
GetLastOpenTickets, GetLastClosedTickets) {

    $scope.offsetMax = 25;
    $scope.limitMax = 25;

    $scope.offsetOpen = 0;
    $scope.limitOpen = 25;
    $scope.offsetClosed = 0;
    $scope.limitClosed = 25;

    $scope.lastOpenTickets = [];
    $scope.lastClosedTickets = [];

    //Refresher
    $scope.doRefreshOpenTickets = function () {
        $scope.lastOpenTickets = [];
        $scope.offsetOpen = 0;
        $scope.limitOpen = 25;
        $scope.offsetClosed = 0;
        $scope.limitClosed = 25;

        $scope.GetLastOpenTickets();
        console.log("View refreshed !");
    }

    $scope.doRefreshClosedTickets = function () {
        $scope.lastClosedTickets = [];
        $scope.offsetOpen = 0;
        $scope.limitOpen = 25;
        $scope.offsetClosed = 0;
        $scope.limitClosed = 25;

        $scope.GetLastClosedTickets();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.userDatas = $rootScope.userDatas;

    /*
    ** Get last open ticket information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastOpenTickets = function () {
        $rootScope.showLoading();
        GetLastOpenTickets.get({
            id: $scope.projectId,
            token: $rootScope.userDatas.token,
            offset: $scope.offsetOpen,
            limit: $scope.limitOpen
        }).$promise
            .then(function (data) {
                console.log('Get last open tickets info successful !');
                if (data.data.array[0] != null) {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastOpenTickets.push(data.data.array[i]);
                    }
                    $scope.offsetOpen += $scope.offsetMax;
                    $scope.limitOpen += $scope.limitMax;
                }
            })
            .catch(function (error) {
                console.error('Get last open tickets info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetLastOpenTickets();

    /*
    ** Get last closed ticket information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastClosedTickets = function () {
        $rootScope.showLoading();
        GetLastClosedTickets.get({
            id: $scope.projectId,
            token: $rootScope.userDatas.token,
            offset: $scope.offsetClosed,
            limit: $scope.limitClosed
        }).$promise
            .then(function (data) {
                console.log('Get last closed tickets info successful !');
                if (data.data.array[0] != null) {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastClosedTickets.push(data.data.array[i]);
                    }
                    $scope.offsetClosed += $scope.offsetMax;
                    $scope.limitClosed += $scope.limitMax;
                }
            })
            .catch(function (error) {
                console.error('Get last closed tickets info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetLastClosedTickets();
})