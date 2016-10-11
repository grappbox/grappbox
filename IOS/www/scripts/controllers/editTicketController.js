/*
    Summary: Edit ticket on bugtracker Controller
*/

angular.module('GrappBox.controllers')

.controller('EditTicketCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicActionSheet, Bugtracker, Toast, Projects) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.bugtracker;
    });

    //Refresher
    $scope.doRefresh = function () {
        $scope.usersOnTicket = [];
        $scope.usersToAdd = [];
        $scope.usersToRemove = [];
        $scope.ticket = {};
        $scope.userList = {};

        $scope.GetTicketInfo();
        $scope.GetTagsOnProject();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.ticketId = $stateParams.ticketId;

    $scope.usersOnTicket = [];

    /*
    ** Get ticket information
    ** Method: GET
    */
    $scope.ticket = {};
    $scope.userList = {};
    $scope.GetTicketInfo = function () {
        //$rootScope.showLoading();
        Bugtracker.GetTicketInfo().get({
            id: $scope.ticketId
        }).$promise
            .then(function (data) {
                console.log('Get ticket info successful !');
                $scope.ticket = data.data;
                console.log($scope.ticket);
                for (var i = 0; i < $scope.ticket.users.length; i++) { // push all users assigned to ticket in usersOnTicket
                    $scope.usersOnTicket.push({
                        id: $scope.ticket.users[i].id,
                        firstname: $scope.ticket.users[i].firstname,
                        lastname: $scope.ticket.users[i].lastname,
                        //name: $scope.ticket.users[i].name,
                        email: $scope.ticket.users[i].email,
                        avatar: $scope.ticket.users[i].avatar,
                        checked: true
                    });
                }
                Projects.Users().get({
                    id: $scope.projectId
                }).$promise
                .then(function (data) {
                    $scope.userList = data.data.array;
                    console.log('Get project members successful !');
                    console.log(data.data.array);
                    for (var i = 0; i < $scope.userList.length; i++) { // for each user on the project...
                        for (var j = 0, isIn = false; j < $scope.usersOnTicket.length; j++) { // ... and for each user already in user assigned tab...
                            if ($scope.userList[i].id == $scope.usersOnTicket[j].id) // ... if a user from project is in user from ticket ...
                            {
                                isIn = true;
                                break;
                            }
                        }
                        if (!isIn) { // if he isn't, push him in users on ticket tab
                            $scope.usersOnTicket.push({
                                id: $scope.userList[i].id,
                                firstname: $scope.userList[i].firstname,
                                lastname: $scope.userList[i].lastname,
                                //name: $scope.userList[i].firstname + " " + $scope.userList[i].lastname,
                                email: "",
                                avatar: "",
                                checked: false
                            });
                        }
                    }
                })
                .catch(function (error) {
                    console.error('Get project members failed ! Reason: ' + error.status + ' ' + error.statusText);
                    $scope.$broadcast('scroll.refreshComplete');
                    //$rootScope.hideLoading();
                })
            })
            .catch(function (error) {
                console.error('Get ticket info or get users on project failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTicketInfo();

    /*
    ** Edit ticket
    ** Method: PUT
    */
    $scope.editTicketData = {};
    $scope.EditTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.EditTicket().update({
            id: $scope.ticketId,
            data: {
                title: $scope.ticket.title,
                description: $scope.ticket.description,
                addTags: [],
                removeTags: [],
                addUsers: [],
                removeUsers: [],
                clientOrigin: false
            }
        }).$promise
            .then(function (data) {
                console.log('Edit ticket successful !');
                Toast.show("Ticket edited");
                $scope.editTicketData = data.data;
            })
            .catch(function (error) {
                console.error('Edit ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Ticket error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Get all tags
    ** Method: GET
    */
    $scope.tagsOnProject = {};
    $scope.GetTagsOnProject = function () {
        //$rootScope.showLoading();
        Bugtracker.GetTagsOnProject().get({
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get tags on project successful !');
                $scope.tagsOnProject = data.data.array;
                if (data.data.array.length == 0)
                    $scope.noTag = "There is no tag linked to project.";
                else
                    $scope.noTag = false;
                console.log($scope.tagsOnProject);
            })
            .catch(function (error) {
                console.error('Get tags on project failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTagsOnProject();

    $scope.tagToAdd = {};

    /*
    ** Create tag
    ** Method: POST
    */
    $scope.createTag = {};
    $scope.createTagData = {};
    $scope.CreateTag = function () {
        //$rootScope.showLoading();
        Bugtracker.CreateTag().save({
            data: {
                projectId: $scope.projectId,
                name: $scope.createTag.name,
                color: ""
            }
        }).$promise
            .then(function (data) {
                console.log('Create tag successful !');
                Toast.show("Tag created");
                $scope.noTag = false;
                $scope.project = data.data;
                $scope.GetTagsOnProject();
            })
            .catch(function (error) {
                console.error('Create tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Assign a tag to a ticket
    ** Method: PUT
    */
    $scope.assignTagData = {};
    $scope.AssignTag = function () {
        //$rootScope.showLoading();
        Bugtracker.AssignTag().update({
            bugId: $scope.ticketId,
            data: {
                tagId: $scope.tagToAdd.id
            }
        }).$promise
            .then(function (data) {
                console.log('Assign tag successful !');
                Toast.show("Tag assigned");
                $scope.assignTagData = data.data;
                $scope.GetTicketInfo();
            })
            .catch(function (error) {
                console.error('Assign tag failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag assign error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    $scope.tagToRemove = {};
    // Delete tag action sheet
    $scope.showRemoveTagActionSheet = function (tag_id) {
        $scope.tagToRemove.id = tag_id;
        // Show the action sheet
        $ionicActionSheet.show({
            destructiveText: 'Remove tag',
            titleText: 'Tag action',
            cancelText: 'Cancel',
            destructiveButtonClicked: function () {
                $scope.RemoveTagFromTicket();
                return true;
            }
        });
    }

    /*
    ** Remove tag from ticket
    ** Method: DELETE
    */
    $scope.removeTagFromTicketData = {};
    $scope.RemoveTagFromTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.RemoveTagFromTicket().delete({
            bugId: $scope.ticketId,
            tagId: $scope.tagToRemove.id
        }).$promise
            .then(function (data) {
                console.log('Remove tag from ticket successful !');
                Toast.show("Tag removed from ticket");
                $scope.deleteTagData = data.data;
                $scope.GetTicketInfo();
            })
            .catch(function (error) {
                console.error('Remove tag from ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Tag remove error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    $scope.arrayObjectIndexOf = function (arr, obj) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i].id == obj) {
                console.log(i);
                return i;
            }
        };
        return -1;
    }

    $scope.usersToAdd = [];
    $scope.usersToRemove = [];

    $scope.AddOrRemoveUser = function (user_id, isChecked, index) {
        console.log("user_id: " + user_id + " isChecked: " + isChecked + " index: " + index);

        if (isChecked) {
            if ($scope.arrayObjectIndexOf($scope.ticket.users, user_id) == -1) {
                $scope.usersToAdd.push(user_id);
            }
            else {
                var _index = $scope.usersToRemove.indexOf(user_id);
                $scope.usersToRemove.splice(_index, 1);
            }
        }
        else if (!isChecked) {
            if ($scope.arrayObjectIndexOf($scope.ticket.users, user_id) != -1) {
                $scope.usersToRemove.push(user_id);
            }
            else {
                var _index = $scope.usersToAdd.indexOf(user_id);
                $scope.usersToAdd.splice(_index, 1);
            }
        }
    };

    /*
    ** (Un)Assign users to ticket
    ** Method: PUT
    */
    $scope.setParticipantsData = {};
    $scope.SetUsersToTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.SetUsersToTicket().update({
            id: $scope.ticketId,
            data: {
                toAdd: $scope.usersToAdd,
                toRemove: $scope.usersToRemove
            }
        }).$promise
            .then(function (data) {
                console.log('(Un)Assign users to ticket successful !');
                Toast.show("User (un)assigned");
                $scope.setParticipantsData = data.data;
                $scope.usersOnTicket = [];
                $scope.usersToAdd = [];
                $scope.usersToRemove = [];
                $scope.GetTicketInfo();
            })
            .catch(function (error) {
                console.error('(Un)Assign users to ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("User (un)assign error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
})