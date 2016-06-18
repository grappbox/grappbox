/*
    Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state, $stateParams, Dashboard, Users) {
    
    $scope.doRefreshTeamOccupation = function () {
        $scope.GetTeamOccupation();
        console.log("View refreshed !");
    }

    $scope.doRefreshNextMeetings = function () {
        $scope.GetNextMeetings();
        console.log("View refreshed !");
    }

    $scope.doRefreshGlobalProgress = function () {
        $scope.GetGlobalProgress();
        console.log("View refreshed !");
    }

    console.log("PROJECTID = " + $stateParams.projectId);
    $rootScope.projectId = $stateParams.projectId;
    $scope.projectId = $stateParams.projectId;

    //Get Team Occupation
    $scope.teamOccupationTab = {};
    $scope.GetTeamOccupation = function () {
        $rootScope.showLoading();
        Dashboard.TeamOccupation().get({
            token: $rootScope.userDatas.token,
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get team occupation list successful !');
                console.log(data.data.array);
                $scope.teamOccupationTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noTeam = "You don't have team.";
            })
            .catch(function (error) {
                console.error('Get team occupation list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTeamOccupation();

    //Get Next Meetings
    $scope.nextMeetingsTab = {};
    $scope.GetNextMeetings = function () {
        $rootScope.showLoading();
        Dashboard.NextMeetings().get({
            token: $rootScope.userDatas.token,
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data.data);
                $scope.nextMeetingsTab = data.data.array;
                if (data.data.array.length < 1)
                    $scope.noMeeting = "You don't have meeting.";
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetNextMeetings();

    //Get Global Progress
    /*$scope.globalProgressTab = {};
    $scope.GetGlobalProgress = function () {
        $rootScope.showLoading();
        Dashboard.GlobalProgress().get({ token: $rootScope.userDatas.token }).$promise
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
        $rootScope.showLoading();
        Users.Avatars().get({
            token: $rootScope.userDatas.token,
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
                $rootScope.hideLoading();
            })
    }
    $scope.GetUsersAvatars();
})