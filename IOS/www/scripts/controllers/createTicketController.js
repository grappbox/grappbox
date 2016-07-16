/*
    Create ticket on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('CreateTicketCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Bugtracker) {
    $scope.ticket = {};
    $scope.message = $stateParams.message;
    if ($scope.message != null) {
        $scope.ticket.description = $scope.message.message;
        $scope.ticket.title = $scope.message.title;
    }

    /*
    ** Create ticket
    ** Method: POST
    */
    $scope.CreateTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.CreateTicket().save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $stateParams.projectId,
                title: $scope.ticket.title,
                description: $scope.ticket.description,
                stateId: 1,
                stateName: "Open",
                clientOrigin: false
            }
        }).$promise
            .then(function (data) {
                console.log('Create ticket successful !');
                Toast.show("Ticket created");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.bugtracker', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                Toast.show("Ticket error");
                console.error('Create ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})