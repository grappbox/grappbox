/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')
.controller('TimelinesCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, $ionicPopup, Toast, Timeline) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.timeline;
    });

    $scope.offsetMax = 25;
    $scope.limitMax = 25;

    $scope.offsetTeam = 0;
    $scope.offsetCustomer = 0;

    $scope.lastMessagesTeam = [];
    $scope.lastMessagesCustomer = [];

    //Refresher
    $scope.doRefreshTeamTimeline = function () {
        $scope.lastMessagesTeam = [];
        $scope.offsetTeam = 0;
        $scope.offsetCustomer = 0;

        $scope.GetLastMessagesTeam();
        console.log("View refreshed !");
    }

    $scope.doRefreshCustomerTimeline = function () {
        $scope.lastMessagesCustomer = [];
        $scope.offsetTeam = 0;
        $scope.offsetCustomer = 0;

        $scope.GetLastMessagesCustomer();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;
    $scope.message = {};
    $scope.userConnectedId = $rootScope.userDatas.id;

    /*
    ** Get timelines
    ** Method: GET
    */
    $scope.timelineIds = [];
    $scope.GetTimelines = function () {
        //$rootScope.showLoading();
        Timeline.List().get({
            id: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Get timelines info successful !');
                if (data.data.array[0] != null) {
                    for (var i = 0; i < data.data.array.length; i++)
                        $scope.timelineIds.push(data.data.array[i].id);
                }
                $scope.GetLastMessagesTeam();
                $scope.GetLastMessagesCustomer();
            })
            .catch(function (error) {
                console.error('Get timelines info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.log(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetTimelines();

    /*
    ** Get last messages team information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastMessagesTeam = function (isMore) {
        if (isMore)
            $rootScope.showLoading();
        Timeline.LastMessages().get({
            id: $scope.timelineIds[0],
            offset: $scope.offsetTeam,
            limit: $scope.limitMax
        }).$promise
            .then(function (data) {
                console.log('Get last messages team info successful !');
                if (data.data.array.length == 0 && $scope.lastMessagesTeam.length == 0) {
                    $scope.noMessageTeam = "There is no message in team timeline."
                }
                else {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastMessagesTeam.push(data.data.array[i]);
                    }
                    if (data.data.array.length < $scope.offsetMax) {
                        $scope.offsetTeam += data.data.array.length;
                    }
                    else {
                        $scope.offsetTeam += $scope.offsetMax;
                    }
                    $scope.noMessageTeam = false;
                    //$scope.offsetTeam += $scope.offsetMax;
                    //$scope.limitTeam += $scope.limitMax;
                }
                console.log(data.data.array);
            })
            .catch(function (error) {
                console.error('Get last messages team info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Get last messages customer information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastMessagesCustomer = function (isMore) {
        if (isMore)
            $rootScope.showLoading();
        Timeline.LastMessages().get({
            id: $scope.timelineIds[1],
            offset: $scope.offsetCustomer,
            limit: $scope.limitMax
        }).$promise
            .then(function (data) {
                console.log('Get last messages customer info successful !');
                if (data.data.array.length == 0 && $scope.lastMessagesCustomer.length == 0) {
                    $scope.noMessageCustomer = "There is no message in customer timeline."
                }
                else {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastMessagesCustomer.push(data.data.array[i]);
                    }
                    if (data.data.array.length < $scope.offsetMax) {
                        $scope.offsetCustomer += data.data.array.length;
                    }
                    else {
                        $scope.offsetCustomer += $scope.offsetMax;
                    }
                    $scope.noMessageCustomer = false;
                    //$scope.offsetCustomer += $scope.offsetMax;
                    //$scope.limitCustomer += $scope.limitMax;
                }
            })
            .catch(function (error) {
                console.error('Get last messages customer info failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Edit base message
    ** Method: PUT
    */
    $scope.editMessageData = {};
    $scope.EditMessage = function (mess) {
        //$rootScope.showLoading();
        Timeline.EditMessage().update({
            id: mess.timelineId,
            data: {
                messageId: mess.id,
                title: mess.title,
                message: mess.message
            }
        }).$promise
            .then(function (data) {
                console.log('Edit message successful !');
                Toast.show("Message edited");
                $scope.editTicketData = data.data;
            })
            .catch(function (error) {
                console.error('Edit message failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Message edition error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Edit comment on timeline
    ** Method: PUT
    */
    $scope.editCommentData = {};
    $scope.EditCommentOnTimeline = function (com, mess) {
        //$rootScope.showLoading();
        Timeline.EditComment().update({
            id: mess.timelineId,
            data: {
                commentId: com.id,
                comment: com.comment
            }
        }).$promise
            .then(function (data) {
                console.log('Edit comment on timeline successful !');
                Toast.show("Comment edited");
                $scope.editCommentData = data.data;
            })
            .catch(function (error) {
                console.error('Edit comment on timeline failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                Toast.show("Comment edition error");
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Archive message and all its comments
    ** Method: DELETE
    */
    $scope.archiveMessageData = {};
    $scope.ArchiveMessage = function (message) {
        //$rootScope.showLoading();
        Timeline.ArchiveMessageAndComments().delete({
            id: message.timelineId,
            messageId: message.id
        }).$promise
            .then(function (data) {
                console.log('Archive message successful !');
                Toast.show("Message deleted");
                $scope.archiveMessageData = data.data;
                //$state.go($state.currentState, { projectId: $stateParams.projectId }, { reload: true });
            })
            .catch(function (error) {
                console.error('Archive message failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Message delete error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Delete comment
    ** Method: DELETE
    */
    $scope.archiveCommentData = {};
    $scope.ArchiveComment = function (com, mess) {
        //$rootScope.showLoading();
        Timeline.DeleteComment().delete({
            id: mess.timelineId,
            commentId: com.id
        }).$promise
            .then(function (data) {
                console.log('Delete comment successful !');
                Toast.show("Comment deleted");
                $scope.archiveMessageData = data.data;
                $scope.GetCommentsOnTimeline(mess);
            })
            .catch(function (error) {
                console.error('Delete comment failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                Toast.show("Comment delete error");
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Get comments on timeline
    ** Method: GET
    */
    $scope.comments = {};
    $scope.GetCommentsOnTimeline = function (message) {
        //$rootScope.showLoading();
        Timeline.Comments().get({
            id: message.timelineId,
            messageId: message.id
        }).$promise
            .then(function (data) {
                console.log('Get comments on timeline successful !');
                console.log(data.data.array);
                $scope.comments = data.data.array;
            })
            .catch(function (error) {
                console.error('Get comments on timeline failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Post comment on message
    ** Method: POST
    */
    $scope.comment = {};
    $scope.PostComment = function (mess) {
        //$rootScope.showLoading();
        Timeline.PostComment().save({
            id: mess.timelineId,
            data: {
                comment: $scope.comment.message,
                commentedId: mess.id
            }
        }).$promise
            .then(function (data) {
                console.log('Post comment successful !');
                Toast.show("Comment posted");
                $scope.GetCommentsOnTimeline(mess);
            })
            .catch(function (error) {
                console.error('Post comment failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Comment post error");
                console.error(error);
            })
            .finally(function () {
                //$rootScope.hideLoading();
            })
    }

    /*
    ** POPUS
    */
    // Remove confirm popup for deleting message
    $scope.PopupDeleteMessage = function (message) {
        var confirmPopup = $ionicPopup.confirm({
            title: 'Delete Message',
            template: 'Are you sure you want to delete this message ?'
        })
        .then(function (res) {
            if (res) {
                $scope.ArchiveMessage(message);
                console.log("Chose to delete message");
            } else {
                console.log("Chose to keep message");
            }
        })
    }
})