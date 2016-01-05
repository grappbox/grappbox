/*
    Summary: Single project controller
*/

angular.module('GrappBox.controllers')

.controller('ProjectCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicActionSheet, $http,
    ProjectView, AddUserToProject, UsersOnProjectList, GenCustomerAccess, GetCustomersAccessOnProject, RetreiveProject) {

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProjectInfo();
        $scope.GetUsersOnProject();
        $scope.GetCustomersOnProject();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;

    /*
    ** Get project information
    ** Method: GET
    */
    $scope.projectInfo = {};
    $scope.GetProjectInfo = function () {
        ProjectView.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project info successful !');
                $scope.projectInfo = data;
            })
            .catch(function (error) {
                console.error('Get project info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.log('projectId: ' + $scope.projectId);
            })
    }
    $scope.GetProjectInfo();

    /*
    ** Get project members
    ** Method: GET
    */
    $scope.userList = {};
    $scope.GetUsersOnProject = function () {
        UsersOnProjectList.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get users on project list successful !');
                $scope.userList = data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get users on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
    }
    $scope.GetUsersOnProject();

    /*
    ** Add a user to project
    ** Method: PUT
    */
    $scope.userToAdd = {};
    $scope.AddUserToProject = function () {
        AddUserToProject.update({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            userEmail: $scope.userToAdd.userEmail,
        }).$promise
            .then(function (data) {
                console.log('Add user to project successful !');
                $scope.userList = data;
            })
            .then(function () {
                $scope.GetUsersOnProject;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Add user to project failed ! Reason: ' + error.status + ' ' + error.statusText);
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
                $scope.removeUserFromProject();
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
    $scope.removeUserFromProject = function () {
        $http.delete($rootScope.API + 'projects/removeusertoproject', {
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                userId: $scope.user_id
            }
        })
        .then(function (data) {
            console.log('Remove user from project successful !');
            $scope.userRemoveData = data;
        })
        .then(function () {
            $scope.GetUsersOnProject();
        })
        .then(function () {
            $scope.$broadcast('scroll.refreshComplete');
        })
        .catch(function (error) {
            console.error('Remove user from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
            console.error('token: ' + $rootScope.userDatas.token + ', projectId: ' + $scope.projectId + ', userId: ' + $scope.user_id);
        })
    }

    /*
    ** Get customers accesses on project
    ** Method: GET
    */
    $scope.customersList = {};
    $scope.GetCustomersOnProject = function () {
        GetCustomersAccessOnProject.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get customers on project list successful !');
                $scope.customersList = data;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get customers on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }
    $scope.GetCustomersOnProject();

    /*
    ** Generate customer access
    ** Method: POST
    */
    $scope.customerAccessToAdd = {};
    $scope.GenerateAccess = function () {
        GenCustomerAccess.save({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            name: $scope.customerAccessToAdd.accessName
        }).$promise
            .then(function (data) {
                console.log('Generate a customer access successful !');
                $scope.userList = data;
            })
            .then(function () {
                $scope.GetCustomersOnProject();
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Generate a customer access failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
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
                $scope.deleteCustomerAccess();
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
    $scope.deleteCustomerAccess = function () {

        $http.delete($rootScope.API + 'projects/delcustomeraccess', {
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                customerAccessId: $scope.customer_id
            }
        })
        .then(function (data) {
            console.log('Delete customer access from project successful !');
            $scope.deleteCustomerAccessData = data;
        })
        .then(function () {
            $scope.GetCustomersOnProject();
        })
        .then(function () {
            $scope.$broadcast('scroll.refreshComplete');
        })
        .catch(function (error) {
            console.error('Delete customer access from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
            console.error('token: ' + $rootScope.userDatas.token + ', projectId: ' + $scope.projectId + ', userId: ' + $scope.customer_id);
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
                $scope.deleteProject();
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
    $scope.deleteProject = function () {
        $http.delete($rootScope.API + 'projects/delproject', {
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId
            }
        })
        .then(function (data) {
            console.log('Delete project successful !');
            $scope.deleteProjectData = data;
        })
        .then(function () {
            $scope.GetProjectInfo();
        })
        .then(function () {
            $scope.$broadcast('scroll.refreshComplete');
        })
        .catch(function (error) {
            console.error('Delete project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error('token: ' + $rootScope.userDatas.token + ', projectId: ' + $scope.projectId);
            console.error(error);
        })
    }

    /*
    ** Get customers accesses on project
    ** Method: GET
    */
    $scope.RetreiveProject = function () {
        RetreiveProject.get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Retreive project successful !');
                $scope.customersList = data;
            })
            .then(function () {
                $scope.GetProjectInfo();
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Retreive project failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
    }
})

