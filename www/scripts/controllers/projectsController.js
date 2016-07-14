﻿/*
    Summary: Projects list
*/

angular.module('GrappBox.controllers')
// PROJECTS LIST
.controller('ProjectsListCtrl', function ($scope, $rootScope, $state, $stateParams, Projects, Users) {

    $scope.doRefresh = function () {
        $scope.GetProjects();
        console.log("View refreshed !");
    }

    $rootScope.hasProject = false;

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
                console.log(data.data);
                $scope.projectsTab = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noProject = "You don't have any project.";
                else {
                    $scope.noProject = false;
                    for (var i = 0; i < $scope.projectsTab.length; i++) {
                        $scope.GetProjectLogo(i, $scope.projectsTab[i].project_id);
                    }
                }
            })
            .catch(function (error) {
                console.error('Get projects list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjects();

    /*
    ** Get logo
    ** Method: GET
    */
    $scope.profileLogo = {};
    $scope.GetProjectLogo = function (index, projectId) {
        //$rootScope.showLoading();
        Projects.Logo().get({
            token: $rootScope.userDatas.token,
            projectId: projectId
        }).$promise
            .then(function (data) {
                console.log('Get logo successful !');
                if (data.data.logo) {
                    $scope.projectsTab[index].logo = data.data.logo;
                }
                /*if (data.data.logo && $rootScope.isBase64(data.data.logo)) {

                }*/
            })
            .catch(function (error) {
                console.error('Get logo failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
})