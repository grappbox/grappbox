/*
    Summary: Users on a specific role on a project controller
*/

angular.module('GrappBox.controllers')

.controller('UsersOnRoleCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicActionSheet, Toast, Roles) {

    //Refresher
    $scope.doRefresh = function () {

        $scope.GetUsersForRole();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.roleId = $stateParams.roleId;
    $scope.role = $stateParams.role;

    /*
    ** Delete role
    ** Method: DELETE
    */
    $scope.deleteRoleData = {};
    $scope.DeleteProjectRole = function () {
        //$rootScope.showLoading();
        Roles.Delete().delete({
            token: $rootScope.userDatas.token,
            id: $scope.roleId
        }).$promise
        .then(function (data) {
            console.log('Delete role successful !');
            Toast.show("Role deleted");
            $scope.deleteRoleData = data;
            $state.go('app.roles', { projectId: $scope.projectId });
        })
        .catch(function (error) {
            console.error('Delete role failed ! Reason: ' + error.status + ' ' + error.statusText);
            Toast.show("Role delete error");
        })
        .finally(function () {
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Get users who are / aren't on the role on project
    ** Method: GET
    */
    $scope.usersForRole = {};
    $scope.GetUsersForRole = function () {
        $rootScope.showLoading();
        Roles.UsersAssignedOrNot().get({
            token: $rootScope.userDatas.token,
            roleId: $scope.roleId
        }).$promise
            .then(function (data) {
                console.log('Get users for role successful !');
                $scope.usersForRole = data.data;
                if (data.data.users_assigned.length == 0)
                    $scope.userAssignedOnRole = "There are no user assigned on this role."
                else
                    $scope.userAssignedOnRole = false;
                if (data.data.users_non_assigned.length == 0)
                    $scope.userNonAssignedOnRole = "All users are assigned to this role."
                else
                    $scope.userNonAssignedOnRole = false;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Get users for role failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
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
        //$rootScope.showLoading();
        console.log($scope.userToAdd.userId);
        Roles.Assign().save({
            data: {
                token: $rootScope.userDatas.token,
                userId: $scope.userToAdd.userId,
                roleId: $scope.roleId
            }
        }).$promise
            .then(function (data) {
                console.log('Assign user on role successful !');
                Toast.show("User assigned on role");
                $scope.userAssignedOnRole = false;
                $scope.userAssignedData = data.data;
                $scope.GetUsersForRole();
                console.log(data);
            })
            .catch(function (error) {
                console.error('Assign user on role failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("User assignation on role error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Remove member from current role
    ** Method: DELETE
    */
    $userRemoveRoleData = {};
    $scope.RemoveUserFromRole = function () {
        //$rootScope.showLoading();
        Roles.RemoveUser().delete({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            userId: $scope.user_id,
            roleId: $scope.roleId
        }).$promise
        .then(function (data) {
            console.log('Remove user from project successful !');
            Toast.show("Member removed");
            $scope.userRemoveRoleData = data.data;
            $scope.GetUsersForRole();
        })
        .catch(function (error) {
            console.error('Remove user from role failed ! Reason: ' + error.status + ' ' + error.statusText);
            Toast.show("Member removing error");
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Update project role
    ** Method: PUT
    */
    $scope.updatedRoleData = {};
    $scope.UpdateProjectRole = function () {
        //$rootScope.showLoading();
        Roles.Update().update({
            data: {
                token: $rootScope.userDatas.token,
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
            }
        }).$promise
            .then(function (data) {
                console.log('Update project role successful !');
                Toast.show("Project role updated");
                $scope.updatedRoleData = data.data;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Update project role failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Project role update error");
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }

    /*
    ** ACTION SHEETS
    */

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

    /*
    ** POPUPS
    */

    // Remove confirm popup for deleting role
    $scope.PopupDeleteRole = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete Role',
            template: 'Are you sure you want to delete this role ?'
        })
        .then(function (res) {
            if (res) {
                $scope.DeleteProjectRole();
                console.log("Chose to delete role");
            } else {
                console.log("Chose to keep role");
            }
        })
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
})