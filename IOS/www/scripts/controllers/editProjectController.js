/*
    Summary: Edit single project controller
*/

angular.module('GrappBox.controllers')

.controller('EditProjectCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicHistory, Toast, Projects) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProjectInfo();
        console.log("View refreshed !");
    }

    /*
    ** Get project logo
    ** Method: GET
    */
    $scope.projectLogo = {};
    $scope.GetProjectLogo = function () {
        //$rootScope.showLoading();
        Projects.Logo().get({
            id: $stateParams.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project logo successful !');
                $scope.projectLogo.logo = data.data.logo;
            })
            .catch(function (error) {
                console.error('Get project logo failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjectLogo();

    /*
    ** Get project information
    ** Method: GET
    */
    $scope.project = {};
    $scope.GetProjectInfo = function () {
        //$rootScope.showLoading();
        Projects.Info().get({
            id: $stateParams.projectId
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
                logo: $scope.projectLogo.logo ? $scope.projectLogo.logo.base64 : "",
                phone: $scope.project.phone ? $scope.project.phone : "",
                company: $scope.project.company ? $scope.project.company : "",
                contact_mail: $scope.project.contact_mail ? $scope.project.contact_mail : "",
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