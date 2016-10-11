/*
    Summary: Roles on a project controller
*/

angular.module('GrappBox.controllers')

.controller('RolesCtrl', function ($scope, $rootScope, $state, $stateParams, Toast, Roles) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });

    //Refresher
    $scope.doRefresh = function () {

        $scope.GetProjectRoles();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;

    /*
    ** Get roles on project
    ** Method: GET
    */
    $scope.projectRoles = {};
    $scope.GetProjectRoles = function () {
        //$rootScope.showLoading();
        Roles.List().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project roles successful !');
                $scope.projectRoles = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noRole = "There is no role on project.";
                else
                    $scope.noRole = false;
            })
            .catch(function (error) {
                console.error('Get project roles failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$scope.hideLoading();
            })
    }
    $scope.GetProjectRoles();

    /*
    ** Add new role on project
    ** Method: POST
    */
    $scope.roleType = {
        teamTimeline: 0,
        customerTimeline: 0,
        gantt: 0,
        whiteboard: 0,
        bugtracker: 0,
        event: 0,
        task: 0,
        projectSettings: 0,
        cloud: 0
    }
    $scope.AddNewRole = function () {
        //$rootScope.showLoading();
        Roles.Add().save({
            data: {
                projectId: $scope.projectId,
                name: $scope.roleType.name,
                teamTimeline: $scope.roleType.teamTimeline,
                customerTimeline: $scope.roleType.customerTimeline,
                gantt: $scope.roleType.gantt,
                whiteboard: $scope.roleType.whiteboard,
                bugtracker: $scope.roleType.bugtracker,
                event: $scope.roleType.event,
                task: $scope.roleType.task,
                projectSettings: $scope.roleType.projectSettings,
                cloud: $scope.roleType.cloud
            }
        }).$promise
            .then(function (data) {
                console.log('Add new role successful !');
                Toast.show("Role added");
                $scope.noRole = false;
                $scope.roleAddedData = data.data;
                $scope.GetProjectRoles();
            })
            .catch(function (error) {
                console.error('Add new role failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Role error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$scope.hideLoading();
            })
    }
})