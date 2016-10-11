/*
    Summary: Single project controller
*/

angular.module('GrappBox.controllers')

.controller('ProjectCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicActionSheet, Projects, Toast, Roles, Users) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    });

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetProjectInfo();
        $scope.GetUsersOnProject();
        $scope.GetCustomersOnProject();
        $scope.GetProjectRoles();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    console.log("PROJECT_ID on projectController = " + $scope.projectId);

    /*
    ** Get project logo
    ** Method: GET
    */
    $scope.projectLogo = {};
    $scope.GetProjectLogo = function () {
        //$rootScope.showLoading();
        Projects.Logo().get({
            id: $scope.projectId
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
    $scope.projectInfo = {};
    $scope.GetProjectInfo = function () {
        //$rootScope.showLoading();
        Projects.Info().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get project info successful !');
                $scope.projectInfo = data.data;
            })
            .catch(function (error) {
                console.error('Get project info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjectInfo();

    /*
    ** Get project members
    ** Method: GET
    */
    $scope.userList = {};
    $scope.GetUsersOnProject = function () {
        //$rootScope.showLoading();
        Projects.Users().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get users on project list successful !');
                $scope.userList = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noUser = "There is no user on the project."
                else
                    $scope.noUser = false;
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Get users on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUsersOnProject();

    /*
    ** Add a user to project
    ** Method: POST
    */
    $scope.userToAdd = {};
    $scope.AddUserToProject = function () {
        //$rootScope.showLoading();
        Projects.AddUser().save({
            data: {
                id: $scope.projectId,
                email: $scope.userToAdd.userEmail,
            }
        }).$promise
            .then(function (data) {
                console.log('Add user to project successful !');
                Toast.show("User added");
                $scope.noUser = false;
                $scope.userToAdd = {};
                $scope.GetUsersOnProject();
            })
            .catch(function (error) {
                console.error('Add user to project failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("User add error");
                console.log(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Remove member from current project
    ** Method: DELETE
    */
    $scope.userRemoveData = {};
    $scope.RemoveUserFromProject = function () {
        //$rootScope.showLoading();
        console.log($rootScope.userDatas.token + " " + $scope.projectId + " " + $scope.user_id);
        Projects.RemoveUser().delete({
            projectId: $scope.projectId,
            userId: $scope.user_id
        }).$promise
        .then(function (data) {
            console.log('Remove user from project successful !');
            Toast.show("User removed");
            $scope.userRemoveData = data.data;
            $scope.GetUsersOnProject();
        })
        .catch(function (error) {
            console.error('Remove user from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            Toast.show("User remove problem");
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Get customers accesses on project
    ** Method: GET
    */
    $scope.customersList = {};
    $scope.customersError = "";
    $scope.GetCustomersOnProject = function () {
        //$rootScope.showLoading();
        Projects.CustomersAccesses().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get customers on project list successful !');
                $scope.customersList = data.data.array;
                console.log(data.data.array);
                if (data.data.array.length == 0)
                    $scope.noCustomer = "There is no customer accesses on the project."
                else
                    $scope.noCustomer = false;
                /*if (Object.keys(data.data.array.toJSON()).length < 1)
                    $scope.customersError = "You don't have customers.";*/
            })
            .catch(function (error) {
                console.error('Get customers on project list failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetCustomersOnProject();

    /*
    ** Generate customer access
    ** Method: POST
    */
    $scope.customerAccessToAdd = {};
    $scope.GenerateAccess = function () {
        //$rootScope.showLoading();
        Projects.GenCustomerAccess().save({
            data: {
                projectId: $scope.projectId,
                name: $scope.customerAccessToAdd.accessName
            }
        }).$promise
            .then(function (data) {
                console.log('Generate a customer access successful !');
                Toast.show("Access generated");
                $scope.customerAccessToAdd = {};
                $scope.noCustomer = false;
            })
            .then(function () {
                $scope.GetCustomersOnProject();
            })
            .catch(function (error) {
                console.error('Generate a customer access failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Access generation error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Regenerate client access
    ** Method: DELETE then GET
    */
    $scope.deleteCustomerAccessBeforeRegenInfo = {};
    $scope.DeleteCustomerAccessBeforeRegen = function (customerName) {
        //$rootScope.showLoading();
        Projects.DeleteCustomerAccess().delete({
            projectId: $scope.projectId,
            customerAccessId: $scope.customer_id
        }).$promise
        .then(function (data) {
            console.log('Delete customer access from project before regenerating successful !');
            Toast.show("Customer access deleted");
            $scope.deleteCustomerAccessBeforeRegenInfo = data.data;
            $scope.customerAccessToAdd.accessName = customerName;
            $scope.GenerateAccess();
        })
        .catch(function (error) {
            console.error('Delete customer access from project before regenerating failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Delete customer access from current project
    ** Method: DELETE
    */
    $scope.deleteCustomerAccessData = {};
    $scope.DeleteCustomerAccess = function () {
        //$rootScope.showLoading();
        console.log("token: " + $rootScope.userDatas.token + " projectId: " + $scope.projectId + " customerId: " + $scope.customer_id);
        Projects.DeleteCustomerAccess().delete({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId,
            customerAccessId: $scope.customer_id
        }).$promise
        .then(function (data) {
            console.log('Delete customer access from project successful !');
            Toast.show("Customer access deleted");
            $scope.deleteCustomerAccessData = data.data;
            $scope.GetCustomersOnProject();
        })
        .catch(function (error) {
            console.error('Delete customer access from project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Delete project
    ** Method: DELETE
    */
    $scope.DeleteProject = function () {
        //$rootScope.showLoading();
        Projects.Delete().delete({
            id: $scope.projectId
        }).$promise
        .then(function (data) {
            console.log('Delete project successful !');
            Toast.show("Project deleted");
            $scope.deleteProjectData = data;
            $scope.GetProjectInfo();
        })
        .catch(function (error) {
            console.error('Delete project failed ! Reason: ' + error.status + ' ' + error.statusText);
            console.error('token: ' + $rootScope.userDatas.token + ', projectId: ' + $scope.projectId);
            Toast.show("Project deletion error");
            console.error(error);
        })
        .finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Retreive Project
    ** Method: GET
    */
    $scope.RetreiveProject = function () {
        //$rootScope.showLoading();
        Projects.Retreive().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Retreive project successful !');
                $scope.retreiveProject = data.data;
                Toast.show("Project retreived");
                console.log(data);
                $scope.GetProjectInfo();
            })
            .then(function () {
                $scope.$broadcast('scroll.refreshComplete');
            })
            .catch(function (error) {
                console.error('Retreive project failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Project retreived error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

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
                    $scope.noRole = "There is no role on the project.";
                else
                    $scope.noRole = false;
            })
            .catch(function (error) {
                console.error('Get project roles failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetProjectRoles();

    /*
    ** Get users avatars
    ** Method: GET
    */
    $scope.usersAvatars = {};
    $scope.GetUsersAvatars = function () {
        //$rootScope.showLoading();
        Users.Avatars().get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get members avatars successful !');
                $scope.usersAvatars = data.data.array;
                console.log(data);
            })
            .catch(function (error) {
                console.error('Get members avatars failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetUsersAvatars();

    // Find user avatar
    $scope.findUserAvatar = function (id) {
        for (var i = 0; i < $scope.usersAvatars.length; i++) {
            if ($scope.usersAvatars[i].userId === id) {
                return $scope.usersAvatars[i].avatar;
            }
        }
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
            destructiveText: 'Remove from project',
            titleText: 'Member action',
            cancelText: 'Cancel',
            buttonClicked: function (index) {
                $state.go('app.user', { userId: $scope.user_id, projectId: $scope.projectId });
                return true;
            },
            destructiveButtonClicked: function () {
                $scope.PopupRemoveUserFromProject();
                return true;
            }
        });
    }

    // Customer access action sheet
    $scope.customerAccessToDelete = {};
    $scope.showCustomerActionSheet = function (customer_id, customer_name) {
        $scope.customer_id = customer_id;
        // Show the action sheet
        $ionicActionSheet.show({
            buttons: [{ text: 'Regenerate access' }],
            destructiveText: 'Delete this access',
            titleText: 'Access action',
            cancelText: 'Cancel',
            buttonClicked: function (index) {
                $scope.DeleteCustomerAccessBeforeRegen(customer_name);
                return true;
            },
            destructiveButtonClicked: function () {
                $scope.PopupDeleteCustomerAccess();
                return true;
            }
        });
    }

    /*
    ** POPUPS
    */

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

    // Remove confirm popup for removing customer access from current project
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
})

