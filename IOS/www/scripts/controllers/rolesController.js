/*
    Summary: Roles on a project controller
*/

angular.module('GrappBox.controllers')

.controller('RolesCtrl', function ($scope, $rootScope, $state, $stateParams, $http,
    GetProjectRoles, AddNewRole) {

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
        $rootScope.showLoading();
        GetProjectRoles.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project roles successful !');
                $scope.projectRoles = data;
            })
            .catch(function (error) {
                console.error('Get project roles failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.hideLoading();
            })
    }
    $scope.GetProjectRoles();

    /*
    ** Add new role on project
    ** Method: POST
    */
    $scope.roleType = {};
    $scope.AddNewRole = function () {
        $rootScope.showLoading();
        console.log($scope.roleType.teamTimeline);
        AddNewRole.save({
            _token: $rootScope.userDatas.token,
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
        }).$promise
            .then(function (data) {
                console.log('Add new role successful !');
                $scope.roleAddedData = data;
                $scope.GetProjectRoles();
            })
            .catch(function (error) {
                console.error('Add new role failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.hideLoading();
            })
    }
})