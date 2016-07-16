﻿/*
    Summary: Edit single project controller
*/

angular.module('GrappBox.controllers')

.controller('EditProjectCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProjectInfo();
        console.log("View refreshed !");
    }

    /*
    ** Get project information
    ** Method: GET
    */
    $scope.project = {};
    $scope.GetProjectInfo = function () {
        //$rootScope.showLoading();
        Projects.Info().get({
            token: $rootScope.userDatas.token,
            projectId: $stateParams.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project info successful !');
                $scope.project = data.data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get project info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.log('projectId: ' + $stateParams.projectId);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjectInfo();

    $scope.projectLogo = {};
    $scope.EditProject = function () {
        $rootScope.showLoading();
        Projects.Edit().update({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $stateParams.projectId,
                name: $scope.project.name ? $scope.project.name : "",
                description: $scope.project.description ? $scope.project.description : "",
                logo: $scope.projectLogo.logo.base64 ? $scope.projectLogo.logo.base64 : "",
                phone: $scope.project.phone ? $scope.project.phone : "",
                company: $scope.project.company ? $scope.project.company : "",
                email: $scope.project.email ? $scope.project.email : "",
                facebook: $scope.project.facebook ? $scope.project.facebook : "",
                twitter: $scope.project.twitter ? $scope.project.twitter : "",
                password: $scope.project.password && $scope.project.oldPassword ? $scope.project.password : "",
                oldPassword: $scope.project.password && $scope.project.oldPassword ? $scope.project.oldPassword : ""
            }
        }).$promise
            .then(function (data) {
                console.log('Edit project successful !');
                $rootScope.hideLoading();
                Toast.show("Project edited");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.project', { projectId: $stateParams.projectId });
                });
            })
            .catch(function (error) {
                console.error('Edit project failed ! Reason: ' + error.status + ' ' + error.statusText);
                $rootScope.hideLoading();
                Toast.show("Project error");
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})