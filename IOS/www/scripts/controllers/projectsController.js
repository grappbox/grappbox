/*
    Summary: Projects list
*/

angular.module('GrappBox.controllers')
// PROJECTS LIST
.controller('ProjectsCtrl', function ($scope, $ionicModal, $rootScope, $state, Projects) {
    $scope.projectsTab = {};
    $scope.GetProjects = function () {
        Projects.get({ token: $rootScope.userDatas.token }).$promise
            .then(function (data) {
                console.log('Get projects list successful !');
                $scope.projectsTab = data;
            })
            .catch(function (error) {
                console.error('Get projects list failed ! Reason: ' + error);
            })
    }
    $scope.GetProjects();

    //Search for addProjectModal.html ng-template in projects.html
    $ionicModal.fromTemplateUrl('addProjectModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function (modal) {
        $scope.modal = modal;
    });

    //Open the modal
    $scope.openAddProjectModal = function () {
        $scope.modal.show();
    };

    //Close the modal
    $scope.closeProjectModal = function () {
        $scope.modal.hide();
    };

    //Destroy the modal
    $scope.$on('$destroy', function () {
        $scope.modal.remove();
    });

    //Add a Project in the list
    $scope.addProject = function (elem) {
        $scope.projectsTab.push({
            id: 4,
            name: elem.title
        });
        $scope.modal.hide();
        elem.title = "";
    };

    //Delete a Project
    $scope.onProjectDelete = function (project) {
        $scope.projectsTab.splice($scope.projectsTab.indexOf(project), 1);
    };
})