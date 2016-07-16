/*
    Summary: Profile controller
*/

angular.module('GrappBox.controllers')

.controller('ProfileCtrl', function ($scope, $rootScope, $state, Dashboard, Projects, Users, Roles) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProfileInfo();
        //$scope.GetNextMeetings();
        //$scope.GetProjects();
        //$scope.GetCurrentTasks();
        console.log("View refreshed !");
    }

    /*
    ** Get connected user information
    ** Method: GET
    */
    $scope.profileInfo = {};
    $scope.GetProfileInfo = function () {
        //$rootScope.showLoading();
        Users.ProfileInfo().get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get profile info successful !');
                $scope.profileInfo = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get profile info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProfileInfo();

    /*
    ** Get user avatar
    ** Method: GET
    */
    $scope.userAvatar = {};
    $scope.GetUserAvatar = function () {
        //$rootScope.showLoading();
        Users.Avatar().get({
            token: $rootScope.userDatas.token,
            userId: $rootScope.userDatas.id
        }).$promise
            .then(function (data) {
                console.log('Get user connected avatar successful !');
                $scope.userAvatar = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get user connected avatar failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUserAvatar();

    /*
    ** Get Projects
    ** Method: GET
    */
    $scope.projectsTab = {};
    $scope.GetProjects = function () {
        //$rootScope.showLoading();
        Projects.List().get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get projects list successful !');
                $scope.projectsTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noProject = "You don't have any project.";
                else
                    $scope.noProject = false;
            })
            .catch(function (error) {
                console.error('Get projects list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    //$scope.GetProjects();

    /*
    ** Get Next Meetings
    ** Method: GET
    */
    $scope.nextMeetingsTab = {};
    $scope.nextMeetingsError = "";
    $scope.GetNextMeetings = function () {
        //$rootScope.showLoading();
        Users.NextMeetings().get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data);
                $scope.nextMeetingsTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noMeeting = "You don't have any meeting.";
                else
                    $scope.noMeeting = false;
                /*if (Object.keys(data.toJSON()).length < 1)
                    $scope.nextMeetingsError = "You don't have meeting.";*/
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    //$scope.GetNextMeetings();

    /*
    ** Get current tasks
    ** Method: GET
    */
    $scope.currentTasksTab = {};
    $scope.currentTasksError = "";
    $scope.GetCurrentTasks = function () {
        //$rootScope.showLoading();
        Users.CurrentTasks().get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get current tasks list successful !');
                console.log(data);
                $scope.currentTasksTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noCurrentTask = "You don't have any task.";
                else
                    $scope.noCurrentTask = false;
                /*if (Object.keys(data.toJSON()).length < 1)
                    $scope.currentTasksError = "You don't have task.";*/
            })
            .catch(function (error) {
                console.error('Get current tasks list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    //$scope.GetCurrentTasks();

    /*
    ** Get user connected roles
    ** Method: GET
    */
    $scope.userConnectedRoles = {};
    $scope.userConnectedRolesError = "";
    $scope.GetUserConnectedRoles = function () {
        //$rootScope.showLoading();
        Roles.UserConnectedRoles().get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get user connected roles successful !');
                console.log(data);
                $scope.userConnectedRoles = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noRole = "You don't have any role.";
                else
                    $scope.noRole = false;
                /*if (Object.keys(data.toJSON()).length < 1)
                    $scope.userConnectedRolesError = "You don't have role.";*/
            })
            .catch(function (error) {
                console.error('Get user connected roles failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    //$scope.GetUserConnectedRoles();
})