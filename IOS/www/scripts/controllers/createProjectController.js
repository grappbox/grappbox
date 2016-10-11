/*
    Summary: Page creation controller
*/

angular.module('GrappBox.controllers')

.controller('CreateProjectCtrl', function ($scope, $rootScope, $state, $ionicHistory, Toast, Projects) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });

    $scope.project = {};

    $scope.projectLogo = {};
    $scope.dataProject = {};
    $scope.CreateProject = function () {
        $rootScope.showLoading();
        Projects.Create().save({
            data: {
                name: $scope.project.name,
                description: $scope.project.description ? $scope.project.description : "",
                logo: $scope.projectLogo.logo ? $scope.projectLogo.logo.base64 : "",
                phone: $scope.project.phone ? $scope.project.phone : "",
                company: $scope.project.company ? $scope.project.company : "",
                email: $scope.project.email ? $scope.project.email : "",
                facebook: $scope.project.facebook ? $scope.project.facebook : "",
                twitter: $scope.project.twitter ? $scope.project.twitter : "",
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