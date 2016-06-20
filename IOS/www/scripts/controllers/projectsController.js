/*
    Summary: Projects list
*/

angular.module('GrappBox.controllers')
// PROJECTS LIST
.controller('ProjectsListCtrl', function ($scope, $rootScope, $state, $stateParams, Projects) {

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
    $scope.GetProjects();
})