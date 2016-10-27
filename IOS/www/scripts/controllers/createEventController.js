/*
    Create event on calendar controller
*/

angular.module('GrappBox.controllers')

.controller('CreateEventCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Event) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.calendar;
    });

    $scope.event = {};

    /*
    ** Create event
    ** Method: POST
    */
    $scope.CreateEvent = function () {
        //$rootScope.showLoading();
        Event.Create().save({
            data: {
                projectId: $stateParams.projectId,
                title: $scope.event.title,
                begin: "2016-10-13 12:00:00",
                end: "2016-10-13 13:00:00",
                description: $scope.event.description,
                users: [14]
            }
        }).$promise
            .then(function (data) {
                console.log('Create event successful !');
                Toast.show("Event created");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.calendar', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                Toast.show("Event creation error");
                console.error('Event creation failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Get users on project
    ** Method: GET
    */
    $scope.selectedUsers = [];
    $scope.userList = {};
    $scope.GetUsersOnProject = function () {
        Projects.Users().get({
            id: $stateParams.projectId
        }).$promise
            .then(function (data) {
                $scope.userList = data.data.array;
                console.log('Get project members successful !');
                console.log(data.data.array);
            })
            .catch(function (error) {
                console.error('Get project members failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUsersOnProject();
})