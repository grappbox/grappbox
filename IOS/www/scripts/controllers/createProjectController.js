/*
    Summary: Page creation controller
*/

angular.module('GrappBox.controllers')

.controller('CreateProjectCtrl', function ($scope, $rootScope, $state, Projects) {
    $scope.project = {};

    $scope.CreateProject = function () {
        $rootScope.showLoading();
        Projects.Create().save({
            data: {
                token: $rootScope.userDatas.token,
                name: $scope.project.name,
                description: $scope.project.description,
                logo: $scope.project.logo,
                phone: $scope.project.phone,
                company: $scope.project.company,
                email: $scope.project.email,
                facebook: $scope.project.facebook,
                twitter: $scope.project.twitter,
                password: $scope.project.password
            }
        }).$promise
            .then(function (data) {
                console.log('Create project successful !');
                $state.go('app.projects');
            })
            .catch(function (error) {
                console.error('Create project failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})