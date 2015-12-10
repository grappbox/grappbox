/*
    Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state, TeamOccupation, NextMeetings, GlobalProgress) {

    //Get Team Occupation
    $scope.teamOccupationTab = {};
    $scope.GetTeamOccupation = function () {
        TeamOccupation.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get team occupation list successful !');
                console.log(data);
                $scope.teamOccupationTab = data;
            })
            .catch(function (error) {
                console.error('Get team occupation list failed ! Reason: ' + error);
            })
    }
    $scope.GetTeamOccupation();

    //Get Next Meetings
    $scope.nextMeetingsTab = {};
    $scope.GetNextMeetings = function () {
        NextMeetings.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data);
                $scope.nextMeetingsTab = data;
                if (data.length == 0)
                    $scope.noMeeting = "You have no meeting right now.";
            })
            .catch(function (error) {
                console.error('Get next meetings list failed ! Reason: ' + error);
                $scope.noMeeting = "You have no meeting right now.";
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
            })
            .catch(function (error) {
                console.error('Get global progress list failed ! Reason: ' + error);
            })
    }
    $scope.GetGlobalProgress();
})