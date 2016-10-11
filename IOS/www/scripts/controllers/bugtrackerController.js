/*
    Summary: Bugtracker controller
*/

angular.module('GrappBox.controllers')

.controller('BugtrackerCtrl', function ($scope, $rootScope, $state, $stateParams, Bugtracker) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.bugtracker;
    });

    $scope.offsetMax = 25;
    $scope.limitMax = 25;

    $scope.offsetOpen = 0;
    $scope.offsetClosed = 0;

    $scope.lastOpenTickets = [];
    $scope.lastClosedTickets = [];

    //Refresher
    $scope.doRefreshOpenTickets = function () {
        $scope.lastOpenTickets = [];
        $scope.offsetOpen = 0;
        //$scope.offsetClosed = 0;

        $scope.GetLastOpenTickets();
        console.log("View refreshed !");
    }

    $scope.doRefreshClosedTickets = function () {
        $scope.lastClosedTickets = [];
        //$scope.offsetOpen = 0;
        $scope.offsetClosed = 0;

        $scope.GetLastClosedTickets();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.userDatas = $rootScope.userDatas;

    /*
    ** Get last open ticket information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastOpenTickets = function (isMore) {
        if (isMore)
            $rootScope.showLoading();
        Bugtracker.GetLastOpenTickets().get({
            id: $scope.projectId,
            offset: $scope.offsetOpen,
            limit: $scope.limitMax
        }).$promise
            .then(function (data) {
                console.log('Get last open tickets info successful !');
                console.log(data.data);
                if (data.data.array.length == 0 && $scope.lastOpenTickets.length == 0) {
                    $scope.noOpenTicket = "There are no ticket.";
                }
                else {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastOpenTickets.push(data.data.array[i]);
                    }
                    if (data.data.array.length < $scope.offsetMax) {
                        $scope.offsetOpen += data.data.array.length;
                    }
                    else {
                        $scope.offsetOpen += $scope.offsetMax;
                    }
                    $scope.noOpenTicket = false;
                    //$scope.limitOpen += $scope.limitMax;
                    console.log();
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
    $scope.GetLastClosedTickets = function (isMore) {
        if (isMore)
            $rootScope.showLoading();
        Bugtracker.GetLastClosedTickets().get({
            id: $scope.projectId,
            offset: $scope.offsetClosed,
            limit: $scope.limitMax
        }).$promise
            .then(function (data) {
                console.log('Get last closed tickets info successful !');
                console.log(data.data);
                if (data.data.array.length == 0 && $scope.lastClosedTickets.length == 0) {
                    $scope.noClosedTicket = "There are no ticket.";
                }
                else {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastClosedTickets.push(data.data.array[i]);
                    }
                    console.log("data.data.array.length = " + data.data.array.length);
                    if (data.data.array.length < $scope.offsetMax) {
                        $scope.offsetClosed += data.data.array.length;
                    }
                    else {
                        $scope.offsetClosed += $scope.offsetMax;
                    }
                    $scope.noClosedTicket = false;
                    //$scope.offsetClosed += $scope.offsetMax;
                    //$scope.limitClosed += $scope.limitMax;
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