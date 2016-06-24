﻿/*
    tags on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('TagsCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicActionSheet, Toast, Bugtracker) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetTagsOnProject();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;

    /*
    ** Get all tags
    ** Method: GET
    */
    $scope.tagsOnProject = {};
    $scope.GetTagsOnProject = function () {
        //$rootScope.showLoading();
        Bugtracker.GetTagsOnProject().get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get tags on project successful !');
                $scope.tagsOnProject = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noTag = "There is no tag on the project."
                else
                    $scope.noTag = false;
                console.log($scope.tagsOnProject);
            })
            .catch(function (error) {
                console.error('Get tags on project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTagsOnProject();

    /*
    ** Delete tag from project
    ** Method: DELETE
    */
    $scope.deleteTagFromProjectData = {};
    $scope.DeleteTagFromProject = function (tagId) {
        //$rootScope.showLoading();
        Bugtracker.DeleteTagFromProject().delete({
            token: $rootScope.userDatas.token,
            tagId: tagId
        }).$promise
            .then(function (data) {
                console.log('Delete tag from project successful !');
                $scope.deleteTagData = data.data;
                Toast.show("Tag deleted");
                $scope.GetTagsOnProject();
            })
            .catch(function (error) {
                console.error('Delete tag from project failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag deletion error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Create tag
    ** Method: POST
    */
    $scope.createTag = {};
    $scope.createTagData = {};
    $scope.CreateTag = function () {
        //$rootScope.showLoading();
        Bugtracker.CreateTag().save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                name: $scope.createTag.name
            }
        }).$promise
            .then(function (data) {
                console.log('Create tag successful !');
                Toast.show("Tag created");
                $scope.noTag = false;
                $scope.project = data.data;
                $scope.GetTagsOnProject();
            })
            .catch(function (error) {
                console.error('Create tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag creation error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
})