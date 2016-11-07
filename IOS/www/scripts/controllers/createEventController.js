/*
    Create event on calendar controller
*/

angular.module('GrappBox.controllers')

.controller('CreateEventCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Event) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.calendar;
    });

    $scope.event = {};
    $scope.selectedUsers = [];
    $scope.startTime = new Date();
    $scope.endTime = new Date();

    //Assign user list manually to scope because it seems that Ionic bug with ng-model and <select>
    $scope.updateUserList = function (selectedUsers) {
      $scope.selectedUsers = selectedUsers;
    }

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
                begin: $scope.startTime,
                end: $scope.endTime,
                description: $scope.event.description,
                users: $scope.selectedUsers
            }
        }).$promise
            .then(function (data) {
                console.log('Create event successful !');
                console.log(data);
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
