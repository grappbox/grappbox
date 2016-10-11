/*
    Open or closed ticket on BugTracker controller
*/

angular.module('GrappBox.controllers')

.controller('TicketCtrl', function ($scope, $rootScope, $state, $stateParams, Toast, Bugtracker) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.bugtracker;
    });

    //Refresher
    $scope.doRefresh = function () {
        $scope.GetTicketInfo();
        $scope.GetCommentsOnTicket();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.ticketId = $stateParams.ticketId;
    $scope.userConnectedId = $rootScope.userDatas.id;

    /*
    ** Get ticket information
    ** Method: GET
    */
    $scope.ticket = {};
    $scope.creatorId = {};
    $scope.GetTicketInfo = function () {
        //$rootScope.showLoading();
        Bugtracker.GetTicketInfo().get({
            id: $scope.ticketId
        }).$promise
            .then(function (data) {
                console.log('Get ticket info successful !');
                $scope.ticket = data.data;
                console.log(data.data);
            })
            .catch(function (error) {
                console.error('Get ticket info failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTicketInfo();

    /*
    ** Get comments on ticket
    ** Method: GET
    */
    $scope.comments = {};
    $scope.GetCommentsOnTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.GetCommentsOnTicket().get({
            ticketId: $scope.ticketId
        }).$promise
            .then(function (data) {
                console.log('Get comments on ticket successful !');
                $scope.comments = data.data.array;
            })
            .catch(function (error) {
                console.error('Get comments on ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetCommentsOnTicket();

    /*
    ** Post comment on ticket
    ** Method: POST
    */
    $scope.comment = {};
    $scope.postCommentData = {};
    $scope.PostComment = function () {
        //$rootScope.showLoading();
        Bugtracker.PostComment().save({
            data: {
                comment: $scope.comment.description,
                parentId: $scope.ticketId
            }
        }).$promise
            .then(function (data) {
                console.log('Post comment successful !');
                Toast.show("Comment added");
                $scope.postCommentData = data.data;
                $scope.GetCommentsOnTicket();
            })
            .catch(function (error) {
                console.error('Post comment failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Comment addition error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                $scope.comment = {};
            })
    }

    /*
    ** Reopen ticket
    ** Method: PUT
    */
    $scope.reopenTicketData = {};
    $scope.ReopenTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.ReopenTicket().update({
            id: $scope.ticketId
        }).$promise
            .then(function (data) {
                console.log('Reopen ticket successful !');
                Toast.show("Ticket reopen");
                $scope.reopenTicketData = data.data;
                $scope.GetTicketInfo();
            })
            .catch(function (error) {
                console.error('Reopen ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Ticket reopening error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Close ticket
    ** Method: DELETE
    */
    $scope.closeTicketData = {};
    $scope.CloseTicket = function () {
        //$rootScope.showLoading();
        Bugtracker.CloseTicket().delete({
            id: $scope.ticketId
        }).$promise
        .then(function (data) {
            console.log('Close ticket successful !');
            Toast.show("Ticket closed");
            $scope.closeTicketData = data;
            $scope.GetTicketInfo();
        })
        .catch(function (error) {
            console.error('Close ticket failed ! Reason: ' + error.status + ' ' + error.statusText);
            Toast.show("Ticket closing error");
            console.error(error);
        })
        .finally(function () {
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Delete comment
    ** Method: DELETE
    */
    $scope.closedCommentData = {};
    $scope.CloseComment = function (com) {
        //$rootScope.showLoading();
        Bugtracker.DeleteComment().delete({
            id: com.id
        }).$promise
        .then(function (data) {
            console.log('Close comment successful !');
            Toast.show("Comment closed");
            $scope.closeCommentData = data;
            $scope.GetCommentsOnTicket();
        })
        .catch(function (error) {
            console.error('Close comment failed ! Reason: ' + error.status + ' ' + error.statusText);
            Toast.show("Comment closing error");
            console.error(error);
        })
        .finally(function () {
            //$rootScope.hideLoading();
        })
    }

    /*
    ** Edit comment on ticket
    ** Method: PUT
    */
    $scope.editCommentData = {};
    $scope.EditCommentOnTicket = function (com) {
        //$rootScope.showLoading();
        Bugtracker.EditCommentOnTicket().update({
            id: com.id,
            data: {
                comment: com.comment
            }
        }).$promise
            .then(function (data) {
                console.log('Edit comment successful !');
                Toast.show("Comment edited");
                $scope.editCommentData = data.data;
                $scope.GetCommentsOnTicket();
            })
            .catch(function (error) {
                console.error('Edit comment failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Comment edition error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    $scope.CloseTicketAndComment = function () {
        $scope.PostComment();
        $scope.CloseTicket();
        $scope.comment = {};
    }

    $scope.ReopenTicketAndComment = function () {
        $scope.PostComment();
        $scope.ReopenTicket();
        $scope.comment = {};
    }
})