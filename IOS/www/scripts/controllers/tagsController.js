﻿/*
    tags on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('TagsCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicActionSheet,
    GetTagsOnProject, CreateTag, DeleteTagFromProject) {

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
        $rootScope.showLoading();
        GetTagsOnProject.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get tags on project successful !');
                $scope.tagsOnProject = data.data.array;
                console.log($scope.tagsOnProject);
            })
            .catch(function (error) {
                console.error('Get tags on project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetTagsOnProject();

    $scope.tagToDelete = {};
    $scope.tagClickedId = {};
    // Delete tag action sheet
    $scope.showTagActionSheet = function (tag_id) {
        $scope.tagToDelete.id = tag_id;
        $scope.tagClickedId = tag_id;
        // Show the action sheet
        $ionicActionSheet.show({
            buttons: [{ text: 'Edit tag' }],
            destructiveText: 'Delete tag',
            titleText: 'Tag action',
            cancelText: 'Cancel',
            buttonClicked: function (index) {
                $state.go('app.editTag', { tagId: $scope.tagClickedId, projectId: $scope.projectId });
                return true;
            },
            destructiveButtonClicked: function () {
                $scope.DeleteTagFromProject();
                return true;
            }
        });
    }

    /*
    ** Delete tag from project
    ** Method: DELETE
    */
    $scope.deleteTagFromProjectData = {};
    $scope.DeleteTagFromProject = function () {
        $rootScope.showLoading();
        console.log($scope.tagToDelete.id);
        DeleteTagFromProject.delete({
            token: $rootScope.userDatas.token,
            tagId: $scope.tagToDelete.id
        }).$promise
            .then(function (data) {
                console.log('Delete tag from project successful !');
                $scope.deleteTagData = data.data;
                $scope.GetTicketInfo();
            })
            .catch(function (error) {
                console.error('Delete tag from project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Create tag
    ** Method: POST
    */
    $scope.createTag = {};
    $scope.createTagData = {};
    $scope.CreateTag = function () {
        $rootScope.showLoading();
        CreateTag.save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                name: $scope.createTag.name
            }
        }).$promise
            .then(function (data) {
                console.log('Create tag successful !');
                $scope.project = data.data;
                $scope.GetTagsOnProject();
            })
            .catch(function (error) {
                console.error('Create tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
})