/*
    Open or closed ticket on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('TicketCtrl', function ($scope, $rootScope, $state, $stateParams,
    GetTicketInfo, CloseTicket) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetTicketInfo();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.ticketId = $stateParams.ticketId;

    /*
    ** Get connected user information
    ** Method: GET
    */
    $scope.ticket = {};
    $scope.GetTicketInfo = function () {
        $rootScope.showLoading();
        GetTicketInfo.get({
            id: $scope.ticketId,
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get ticket info successful !');
                $scope.ticket = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get ticket info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTicketInfo();

    /*
    ** Close ticket
    ** Method: DELETE
    */
    $scope.CloseTicket = function () {
        $rootScope.showLoading();
        CloseTicket.delete({
            id: $scope.ticketId,
            token: $rootScope.userDatas.token
        }).$promise
    .then(function (data) {
        console.log('Close ticket successful !');
        $scope.closeTicketData = data;
        $scope.GetTicketInfo();
    })
    .catch(function (error) {
        console.error('Close ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
        console.error(error);
    })
    .finally(function () {
        $rootScope.hideLoading();
    })
    }
})