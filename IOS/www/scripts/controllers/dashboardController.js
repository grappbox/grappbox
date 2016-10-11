/*
    Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state, $stateParams, Toast, Dashboard, Users, Projects) {
    
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });


    $scope.doRefreshTeamOccupation = function () {
        $scope.GetTeamOccupation();
        console.log("View refreshed !");
    }

    $scope.doRefreshNextMeetings = function () {
        $scope.GetNextMeetings();
        console.log("View refreshed !");
    }

    /*$scope.doRefreshGlobalProgress = function () {
        $scope.GetGlobalProgress();
        console.log("View refreshed !");
    }*/

    // Just to know if we are in a project or not for UI
    $rootScope.hasProject = true;

    // Disable back button
    $scope.$on('$ionicView.beforeEnter', function (event, viewData) {
        viewData.enableBack = false;
    });

    console.log("PROJECTID = " + $stateParams.projectId);
    $rootScope.projectId = $stateParams.projectId;
    $scope.projectId = $stateParams.projectId;

    //Get Team Occupation
    $scope.teamOccupationTab = {};
    $scope.GetTeamOccupation = function () {
        //$rootScope.showLoading();
        Dashboard.TeamOccupation().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get team occupation list successful !');
                console.log(data.data.array);
                $scope.teamOccupationTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noTeam = "You don't have team.";
                else
                    $scope.noTeam = false;
            })
            .catch(function (error) {
                console.error('Get team occupation list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTeamOccupation();

    //Get Next Meetings
    $scope.nextMeetingsTab = {};
    $scope.GetNextMeetings = function () {
        //$rootScope.showLoading();
        Dashboard.NextMeetings().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data.data);
                $scope.nextMeetingsTab = data.data.array;
                if (data.data.array.length < 1)
                    $scope.noMeeting = "You don't have meeting.";
                else
                    $scope.noMeeting = false;
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetNextMeetings();

    //Get Global Progress
    /*$scope.globalProgressTab = {};
    $scope.GetGlobalProgress = function () {
        $rootScope.showLoading();
        Dashboard.GlobalProgress().get({
            token: $rootScope.userDatas.token
        }).$promise
            .then(function (data) {
                console.log('Get global progress list successful !');
                console.log(data.data);
                $scope.globalProgressTab = data.data.array;
                if (data.length == 0)
                    $scope.noProject = "You don't have project now.";
            })
            .catch(function (error) {
                console.error('Get global progress list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetGlobalProgress();*/

    /*
    ** Get users avatars
    ** Method: GET
    */
    $scope.usersAvatars = {};
    $scope.GetUsersAvatars = function () {
        //$rootScope.showLoading();
        Users.Avatars().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get members avatars successful !');
                $scope.usersAvatars = data.data.array;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Get members avatars failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUsersAvatars();

    // Find user avatar
    $scope.findUserAvatar = function (id) {
        for (var i = 0; i < $scope.usersAvatars.length; i++) {
            if ($scope.usersAvatars[i].userId === id) {
                return $scope.usersAvatars[i].avatar;
            }
        }
    }

    /*
    ** Get project logo
    ** Method: GET
    */
    $scope.projectLogo = {};
    $scope.GetProjectLogo = function () {
        //$rootScope.showLoading();
        Projects.Logo().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project logo successful !');
                $scope.projectLogo.logo = data.data.logo;
            })
            .catch(function (error) {
                console.error('Get project logo failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjectLogo();

})