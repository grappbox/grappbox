/*
    Summary: Projects list
*/

angular.module('GrappBox.controllers')
// PROJECTS LIST
.controller('ProjectsListCtrl', function ($scope, $rootScope, $state, ProjectsList) {

    $scope.doRefresh = function () {
        $scope.GetProjects();
        console.log("View refreshed !");
    }

    /*
    ** Get Projects
    ** Method: GET
    */
    $scope.projectsTab = {};
    $scope.GetProjects = function () {
        $rootScope.showLoading();
        ProjectsList.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get projects list successful !');
                $scope.projectsTab = data.data.array;
            })
            .catch(function (error) {
                console.error('Get projects list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetProjects();
})