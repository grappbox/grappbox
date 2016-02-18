/*
    Create ticket on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('CreateTicketCtrl', function ($scope, $rootScope, $state, $stateParams,
    CreateTicket) {
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
        $rootScope.showLoading();
        CreateTicket.save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $stateParams.projectId,
                title: $scope.ticket.title,
                description: $scope.ticket.description,
                stateId: 1,
                stateName: "Open"
            }
        }).$promise
            .then(function (data) {
                console.log('Create ticket successful !');
                $state.go('app.bugtracker', { projectId: $stateParams.projectId }, { reload: true });
            })
            .catch(function (error) {
                console.error('Create ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})