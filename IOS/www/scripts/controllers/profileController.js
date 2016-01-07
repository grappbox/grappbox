/*
    Summary: Profile controller
*/

angular.module('GrappBox.controllers')

.controller('ProfileCtrl', function ($scope, $rootScope, $state, GetProfileInfo, ProjectsList, NextMeetings, GetCurrentTasks) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProfileInfo();
        $scope.GetNextMeetings();
        $scope.GetProjects();
        $scope.GetCurrentTasks();
        console.log("View refreshed !");
    }

    /*
    ** Get connected user information
    ** Method: GET
    */
    $scope.profileInfo = {};
    $scope.GetProfileInfo = function () {
        GetProfileInfo.get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get profile info successful !');
                $scope.profileInfo = data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get profile info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetProfileInfo();

    /*
    ** Get Projects
    ** Method: GET
    */
    $scope.projectsTab = {};
    $scope.GetProjects = function () {
        ProjectsList.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get projects list successful !');
                $scope.projectsTab = data;
            })
            .catch(function (error) {
                console.error('Get projects list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetProjects();

    /*
    ** Get Next Meetings
    ** Method: GET
    */
    $scope.nextMeetingsTab = {};
    $scope.nextMeetingsError = "";
    $scope.GetNextMeetings = function () {
        NextMeetings.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data);
                $scope.nextMeetingsTab = data;
                if (Object.keys(data.toJSON()).length < 1)
                    $scope.nextMeetingsError = "You don't have meeting.";
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetNextMeetings();

    /*
    ** Get current tasks
    ** Method: GET
    */
    $scope.currentTasksTab = {};
    $scope.currentTasksError = "";
    $scope.GetCurrentTasks = function () {
        GetCurrentTasks.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get current tasks list successful !');
                console.log(data);
                $scope.currentTasksTab = data;
                if (Object.keys(data.toJSON()).length < 1)
                    $scope.currentTasksError = "You don't have task.";
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get current tasks list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetCurrentTasks();
})