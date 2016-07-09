/*
    Summary: Page creation controller
*/

angular.module('GrappBox.controllers')

.controller('CreateProjectCtrl', function ($scope, $rootScope, $state, $ionicHistory, Toast, Projects) {
    $scope.project = {};

    $scope.dataProject = {};
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
                $scope.dataProject = data.data;
                console.log('Create project successful !');
                $rootScope.hideLoading();
                Toast.show("Project created");
                $ionicHistory.clearCache().then(function () {
                    $state.go('app.dashboard', {projectId: $scope.dataProject.id});
                });
            })
            .catch(function (error) {
                $rootScope.hideLoading();
                Toast.show("Project error");
                console.error('Create project failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }
})