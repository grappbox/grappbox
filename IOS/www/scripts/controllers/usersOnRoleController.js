/*
    Summary: Users on a specific role on a project controller
*/

angular.module('GrappBox.controllers')

.controller('UsersOnRoleCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicActionSheet, $http,
    GetUsersForRole, AssignToRole, UpdateProjectRole) {

    //Refresher
    $scope.doRefresh = function () {

        $scope.GetUsersForRole();

        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.roleId = $stateParams.roleId;
    $scope.role = $stateParams.role;

    // Conversion to boolean so ion-checkbox display well in HTML
    $scope.role.team_timeline = Boolean($scope.role.team_timeline);
    $scope.role.customer_timeline = Boolean($scope.role.customer_timeline);
    $scope.role.gantt = Boolean($scope.role.gantt);
    $scope.role.whiteboard = Boolean($scope.role.whiteboard);
    $scope.role.bugtracker = Boolean($scope.role.bugtracker);
    $scope.role.event = Boolean($scope.role.event);
    $scope.role.task = Boolean($scope.role.task);
    $scope.role.project_settings = Boolean($scope.role.project_settings);
    $scope.role.cloud = Boolean($scope.role.cloud);

    // Remove confirm popup for deleting role
    $scope.PopupDeleteRole = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete Role',
            template: 'Are you sure you want to delete this role ? '
        })
        .then(function (res) {
            if (res) {
                $scope.deleteRole();
                console.log("Chose to delete role");
            } else {
                console.log("Chose to keep role");
            }
        })
    }

    /*
    ** Delete role
    ** Method: DELETE
    */
    $scope.deleteRole = function () {
        $http.delete($rootScope.API + 'roles/delprojectroles', {
            data: {
                _token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                roleId: $scope.roleId
            }
        })
        .then(function (data) {
            console.log('Delete role successful !');
            $scope.deleteRoleData = data;
        })
        .then(function () {
            $state.go('app.roles', { projectId: $scope.projectId });
        })
        .catch(function (error) {
            console.error('Delete role failed ! Reason: ' + error.status + ' ' + error.statusText);
        })
    }

    /*
    ** Get users who are / aren't on the role on project
    ** Method: GET
    */
    $scope.usersForRole = {};
    $scope.GetUsersForRole = function () {
        GetUsersForRole.get({
            token: $rootScope.userDatas.token,
            roleId: $scope.roleId
        }).$promise
            .then(function (data) {
                console.log('Get users for role successful !');
                $scope.usersForRole = data;
                console.log(data);
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get users for role failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }
    $scope.GetUsersForRole();

    /*
    ** Assign a user on the role on project
    ** Method: POST
    */
    $scope.userAssignedData = {};
    $scope.userToAdd = {};
    $scope.AssignToRole = function () {
        console.log($scope.userToAdd.userId);
        AssignToRole.save({
            _token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            userId: $scope.userToAdd.userId,
            roleId: $scope.roleId
        }).$promise
            .then(function (data) {
                console.log('Assign user on role successful !');
                $scope.userAssignedData = data;
                $scope.GetUsersForRole();
                console.log(data);
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Assign user on role failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }

    // Member action sheet
    $scope.showMemberActionSheet = function (user_id) {
        $scope.user_id = user_id;
        // Show the action sheet
        $ionicActionSheet.show({
            buttons: [{ text: 'Member information' }],
            destructiveText: 'Remove from role',
            titleText: 'Member action',
            cancelText: 'Cancel',
            buttonClicked: function (index) {
                $state.go('app.user', { userId: $scope.user_id });
                return true;
            },
            destructiveButtonClicked: function () {
                $scope.PopupRemoveUserFromRole();
                return true;
            }
        });
    }

    // Remove confirm popup for removing member from current role
    $scope.PopupRemoveUserFromRole = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete user from role',
            template: 'Are you sure you want to remove this user from this role ?'
        })
        .then(function (res) {
            if (res) {
                $scope.RemoveUserFromRole();
                console.log("Chose to remove member");
            } else {
                console.log("Chose to keep member");
            }
        })
    }

    /*
    ** Remove member from current role
    ** Method: DELETE
    */
    $scope.RemoveUserFromRole = function () {
        $http.delete($rootScope.API + 'roles/delpersonrole', {
            data: {
                _token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                userId: $scope.user_id,
                roleId: $scope.roleId
            }
        })
        .then(function (data) {
            console.log('Remove user from project successful !');
            $scope.userRemoveRoleData = data;
        })
        .then(function () {
            $scope.GetUsersForRole();
        })
        .then(function () {
            $scope.$broadcast('scroll.refreshComplete');
        })
        .catch(function (error) {
            console.error('Remove user from role failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
        })
    }

    /*
    ** Update project role
    ** Method: PUT
    */
    $scope.updatedRoleData = {};
    $scope.UpdateProjectRole = function () {
        UpdateProjectRole.update({
            _token: $rootScope.userDatas.token,
            roleId: $scope.roleId,
            projectId: $scope.projectId,
            name: $scope.role.name,
            teamTimeline: $scope.role.team_timeline,
            customerTimeline: $scope.role.customer_timeline,
            gantt: $scope.role.gantt,
            whiteboard: $scope.role.whiteboard,
            bugtracker: $scope.role.bugtracker,
            event: $scope.role.event,
            task: $scope.role.task,
            projectSettings: $scope.role.project_settings,
            cloud: $scope.role.cloud
        }).$promise
            .then(function (data) {
                console.log('Update project role successful !');
                $scope.updatedRoleData = data;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Update project role failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }
})