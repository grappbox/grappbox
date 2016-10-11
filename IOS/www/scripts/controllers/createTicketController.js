/*
    Create ticket on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('CreateTicketCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects, Bugtracker) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.bugtracker;
    });

    $scope.ticket = {};
    $scope.message = $stateParams.message;
    if ($scope.message != null) {
        $scope.ticket.description = $scope.message.message;
        $scope.ticket.title = $scope.message.title;
    }
    $scope.logUserList = function () {
        console.log("selectedUsers = ");
        console.log($scope.selectedUsers);
    }
    /*
    ** Create ticket
    ** Method: POST
    */
    $scope.CreateTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.CreateTicket().save({
            data: {
                projectId: $stateParams.projectId,
                title: $scope.ticket.title,
                description: $scope.ticket.description,
                clientOrigin: false,
                users: [],
                tags: []
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
    
    /*
    ** Get all tags
    ** Method: GET
    */
    $scope.selectedTags = {};
    $scope.tagsOnProject = {};
    $scope.GetTagsOnProject = function () {
        //$rootScope.showLoading();
        Bugtracker.GetTagsOnProject().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get tags on project successful !');
                $scope.tagsOnProject = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noTag = "There is no tag linked to project.";
                else
                    $scope.noTag = false;
                console.log($scope.tagsOnProject);
            })
            .catch(function (error) {
                console.error('Get tags on project failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    //$scope.GetTagsOnProject();
})