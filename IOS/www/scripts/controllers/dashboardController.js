/*
    Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state, TeamOccupation, NextMeetings, GlobalProgress) {

    $scope.doRefresh = function () {
        $scope.GetTeamOccupation();
        $scope.GetNextMeetings();
        $scope.GetGlobalProgress();
        console.log("View refreshed !");
    }

    //Get Team Occupation
    $scope.teamOccupationTab = {};
    $scope.GetTeamOccupation = function () {
        TeamOccupation.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get team occupation list successful !');
                console.log(data);
                $scope.teamOccupationTab = data;
                if (data.length == 0)
                    $scope.noTeam = "You don't have team right now.";
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get team occupation list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetTeamOccupation();

    //Get Next Meetings
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
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetNextMeetings();

    //Get Global Progress
    $scope.globalProgressTab = {};
    $scope.GetGlobalProgress = function () {
        GlobalProgress.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get global progress list successful !');
                console.log(data);
                $scope.globalProgressTab = data;
                if (data.length == 0)
                    $scope.noProject = "You don't have project now.";
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get global progress list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetGlobalProgress();
})