/*
    Summary: Single project controller
*/

angular.module('GrappBox.controllers')

.controller('ProjectCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicActionSheet, $http,
    ProjectView, AddUserToProject, UsersOnProjectList, GenCustomerAccess, GetCustomersAccessOnProject, RetreiveProject,
    GetProjectRoles, RemoveUserFromProject, DeleteCustomerAccess, DeleteProject) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProjectInfo();
        $scope.GetUsersOnProject();
        $scope.GetCustomersOnProject();
        $scope.GetProjectRoles();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;

    /*
    ** Get project information
    ** Method: GET
    */
    $scope.projectInfo = {};
    $scope.GetProjectInfo = function () {
        $rootScope.showLoading();
        ProjectView.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project info successful !');
                $scope.projectInfo = data.data;
            })
            .catch(function (error) {
                console.error('Get project info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetProjectInfo();

    /*
    ** Get project members
    ** Method: GET
    */
    $scope.userList = {};
    $scope.GetUsersOnProject = function () {
        $rootScope.showLoading();
        UsersOnProjectList.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get users on project list successful !');
                $scope.userList = data.data.array;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get users on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetUsersOnProject();

    /*
    ** Add a user to project
    ** Method: POST
    */
    $scope.userToAdd = {};
    $scope.AddUserToProject = function () {
        $rootScope.showLoading();
        AddUserToProject.save({
            data: {
                token: $rootScope.userDatas.token,
                id: $scope.projectId,
                email: $scope.userToAdd.userEmail,
            }
        }).$promise
            .then(function (data) {
                console.log('Add user to project successful !');
                $scope.GetUsersOnProject;
            })
            .catch(function (error) {
                console.error('Add user to project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.log(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    // Member action sheet
    $scope.showMemberActionSheet = function (user_id) {
        $scope.user_id = user_id;
        // Show the action sheet
        $ionicActionSheet.show({
            buttons: [{ text: 'Member information' }],
            destructiveText: 'Remove from project',
            titleText: 'Member action',
            cancelText: 'Cancel',
            buttonClicked: function (index) {
                $state.go('app.user', { userId: $scope.user_id });
                return true;
            },
            destructiveButtonClicked: function () {
                $scope.PopupRemoveUserFromProject();
                return true;
            }
        });
    }

    // Remove confirm popup for removing member from current project
    $scope.PopupRemoveUserFromProject = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete user from project',
            template: 'Are you sure you want to remove this user from project ?'
        })
        .then(function (res) {
            if (res) {
                $scope.RemoveUserFromProject();
                console.log("Chose to remove member");
            } else {
                console.log("Chose to keep member");
            }
        })
    }

    /*
    ** Remove member from current project
    ** Method: DELETE
    */
    $scope.userRemoveData = {};
    $scope.RemoveUserFromProject = function () {
        $rootScope.showLoading();
        console.log($rootScope.userDatas.token + " " + $scope.projectId + " " + $scope.user_id);
        RemoveUserFromProject.delete({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            userId: $scope.user_id
        }).$promise
        .then(function (data) {
            console.log('Remove user from project successful !');
            $scope.userRemoveData = data.data;
            $scope.GetUsersOnProject();
        })
        .catch(function (error) {
            console.error('Remove user from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            $rootScope.hideLoading();
        })
    }

    /*
    ** Get customers accesses on project
    ** Method: GET
    */
    $scope.customersList = {};
    $scope.customersError = "";
    $scope.GetCustomersOnProject = function () {
        $rootScope.showLoading();
        GetCustomersAccessOnProject.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get customers on project list successful !');
                $scope.customersList = data.data.array;
                /*if (Object.keys(data.data.array.toJSON()).length < 1)
                    $scope.customersError = "You don't have customers.";*/
            })
            .catch(function (error) {
                console.error('Get customers on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetCustomersOnProject();

    /*
    ** Generate customer access
    ** Method: POST
    */
    $scope.customerAccessToAdd = {};
    $scope.GenerateAccess = function () {
        $rootScope.showLoading();
        GenCustomerAccess.save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                name: $scope.customerAccessToAdd.accessName
            }
        }).$promise
            .then(function (data) {
                console.log('Generate a customer access successful !');
            })
            .then(function () {
                $scope.GetCustomersOnProject();
            })
            .catch(function (error) {
                console.error('Generate a customer access failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    // Customer access action sheet
    $scope.customerAccessToDelete = {};
    $scope.showCustomerActionSheet = function (customer_id) {
        $scope.customer_id = customer_id;
        // Show the action sheet
        $ionicActionSheet.show({
            destructiveText: 'Delete this access',
            titleText: 'Access action',
            cancelText: 'Cancel',
            destructiveButtonClicked: function () {
                $scope.PopupDeleteCustomerAccess();
                return true;
            }
        });
    }

    // Remove confirm popup for removing member from current project
    $scope.PopupDeleteCustomerAccess = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete access from project',
            template: 'Are you sure you want to delete this customer access from project ?'
        })
        .then(function (res) {
            if (res) {
                $scope.DeleteCustomerAccess();
                console.log("Chose to delete access");
            } else {
                console.log("Chose to keep access");
            }
        })
    }

    /*
    ** Delete customer access from current project
    ** Method: DELETE
    */
    $scope.deleteCustomerAccessData = {};
    $scope.DeleteCustomerAccess = function () {
        $rootScope.showLoading();
        console.log("token: " + $rootScope.userDatas.token + " projectId: " + $scope.projectId + " customerId: " + $scope.customer_id);
        DeleteCustomerAccess.delete({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            customerAccessId: $scope.customer_id
        }).$promise
        .then(function (data) {
            console.log('Delete customer access from project successful !');
            $scope.deleteCustomerAccessData = data.data;
            $scope.GetCustomersOnProject();
        })
        .catch(function (error) {
            console.error('Delete customer access from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            $rootScope.hideLoading();
        })
    }

    // Remove confirm popup for deleting project
    $scope.PopupDeleteProject = function () {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete project',
            template: 'Are you sure you want to delete this project ? ' +
                      'It will take 7 days to delete. You still can undo under 7 days.'
        })
        .then(function (res) {
            if (res) {
                $scope.DeleteProject();
                console.log("Chose to delete project");
            } else {
                console.log("Chose to keep project");
            }
        })
    }

    /*
    ** Delete project
    ** Method: DELETE
    */
    $scope.DeleteProject = function () {
        $rootScope.showLoading();
        DeleteProject.delete({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
        .then(function (data) {
            console.log('Delete project successful !');
            $scope.deleteProjectData = data;
            $scope.GetProjectInfo();
        })
        .catch(function (error) {
            console.error('Delete project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error('token: ' + $rootScope.userDatas.token + ', projectId: ' + $scope.projectId);
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            $rootScope.hideLoading();
        })
    }

    /*
    ** Retreive Project
    ** Method: GET
    */
    $scope.RetreiveProject = function () {
        $rootScope.showLoading();
        RetreiveProject.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Retreive project successful !');
                $scope.retreiveProject = data.data;
                console.log(data);
                $scope.GetProjectInfo();
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Retreive project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

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
                $scope.projectRoles = data.data.array;
            })
            .catch(function (error) {
                console.error('Get project roles failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.GetProjectRoles();
})

