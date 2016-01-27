﻿/*
    Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state,
TeamOccupation, NextMeetings, GlobalProgress) {

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

    //Get Team Occupation
    $scope.teamOccupationTab = {};
    $scope.GetTeamOccupation = function () {
        $rootScope.showLoading();
        TeamOccupation.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get team occupation list successful !');
                console.log(data.data);
                $scope.teamOccupationTab = data.data.array;
                if (data.length == 0)
                    $scope.noTeam = "You don't have team right now.";
            })
            .catch(function (error) {
                console.error('Get team occupation list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTeamOccupation();

    //Get Next Meetings
    $scope.nextMeetingsTab = {};
    $scope.nextMeetingsError = "";
    $scope.GetNextMeetings = function () {
        $rootScope.showLoading();
        NextMeetings.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get next meetings list successful !');
                console.log(data.data);
                $scope.nextMeetingsTab = data.data.array;
                if (Object.keys(data.toJSON()).length < 1)
                    $scope.nextMeetingsError = "You don't have meeting.";
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
    $scope.globalProgressTab = {};
    $scope.GetGlobalProgress = function () {
        $rootScope.showLoading();
        GlobalProgress.get({ token: $rootScope.userDatas.token }).$promise
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
    $scope.GetGlobalProgress();
})