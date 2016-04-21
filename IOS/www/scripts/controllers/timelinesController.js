/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')
.controller('TimelinesCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, $ionicPopup, Timeline) {

    $scope.offsetMax = 25;
    $scope.limitMax = 25;

    $scope.offsetTeam = 0;
    $scope.limitTeam = 25;
    $scope.offsetCustomer = 0;
    $scope.limitCustomer = 25;

    $scope.lastMessagesTeam = [];
    $scope.lastMessagesCustomer = [];

    //Refresher
    $scope.doRefreshTeamTimeline = function () {
        $scope.lastMessagesTeam = [];
        $scope.offsetTeam = 0;
        $scope.limitTeam = 25;
        $scope.offsetCustomer = 0;
        $scope.limitCustomer = 25;

        $scope.GetLastMessagesTeam();
        console.log("View refreshed !");
    }

    $scope.doRefreshCustomerTimeline = function () {
        $scope.lastMessagesCustomer = [];
        $scope.offsetTeam = 0;
        $scope.limitTeam = 25;
        $scope.offsetCustomer = 0;
        $scope.limitCustomer = 25;

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
        $rootScope.showLoading();
        Timeline.List().get({
            token: $rootScope.userDatas.token,
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
                $rootScope.hideLoading();
            })
    }
    $scope.GetTimelines();

    /*
    ** Get last messages team information from :offset to :limit
    ** Method: GET
    */
    $scope.GetLastMessagesTeam = function () {
        $rootScope.showLoading();
        Timeline.LastMessages().get({
            id: $scope.timelineIds[0],
            token: $rootScope.userDatas.token,
            offset: $scope.offsetTeam,
            limit: $scope.limitTeam
        }).$promise
            .then(function (data) {
                console.log('Get last messages team info successful !');
                if (data.data.array[0] != null) {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastMessagesTeam.push(data.data.array[i]);
                    }
                    $scope.offsetTeam += $scope.offsetMax;
                    $scope.limitTeam += $scope.limitMax;
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
    $scope.GetLastMessagesCustomer = function () {
        $rootScope.showLoading();
        Timeline.LastMessages().get({
            id: $scope.timelineIds[1],
            token: $rootScope.userDatas.token,
            offset: $scope.offsetCustomer,
            limit: $scope.limitCustomer
        }).$promise
            .then(function (data) {
                console.log('Get last messages customer info successful !');
                if (data.data.array[0] != null) {
                    for (var i = 0; i < data.data.array.length; i++) {
                        $scope.lastMessagesCustomer.push(data.data.array[i]);
                    }
                    $scope.offsetCustomer += $scope.offsetMax;
                    $scope.limitCustomer += $scope.limitMax;
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
        $rootScope.showLoading();
        Timeline.EditMessageOrComment().update({
            data: {
                id: mess.timelineId,
                token: $rootScope.userDatas.token,
                messageId: mess.id,
                title: mess.title,
                message: mess.message
            }
        }).$promise
            .then(function (data) {
                console.log('Edit message successful !');
                $scope.editTicketData = data.data;
            })
            .catch(function (error) {
                console.error('Edit message failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Edit comment on timeline
    ** Method: PUT
    */
    $scope.editCommentData = {};
    $scope.EditCommentOnTimeline = function (com) {
        $rootScope.showLoading();
        Timeline.EditMessageOrComment().update({
            data: {
                id: com.timelineId,
                token: $rootScope.userDatas.token,
                messageId: com.id,
                title: '',
                message: com.message
            }
        }).$promise
            .then(function (data) {
                console.log('Edit comment on timeline successful !');
                $scope.editCommentData = data.data;
            })
            .catch(function (error) {
                console.error('Edit comment on timeline failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    // Remove confirm popup for deleting role
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

    /*
    ** Archive message and all its comments
    ** Method: DELETE
    */
    $scope.archiveMessageData = {};
    $scope.ArchiveMessage = function (message) {
        $rootScope.showLoading();
        Timeline.ArchiveMessageOrComment().delete({
            id: message.timelineId,
            token: $rootScope.userDatas.token,
            messageId: message.id
        }).$promise
            .then(function (data) {
                console.log('Archive message successful !');
                $scope.archiveMessageData = data.data;
                $state.go($state.current, {}, { reload: true });
            })
            .catch(function (error) {
                console.error('Archive message failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Archive comment
    ** Method: DELETE
    */
    $scope.archiveCommentData = {};
    $scope.ArchiveComment = function (com, mess) {
        $rootScope.showLoading();
        Timeline.ArchiveMessageOrComment().delete({
            id: com.timelineId,
            token: $rootScope.userDatas.token,
            messageId: com.id
        }).$promise
            .then(function (data) {
                console.log('Archive comment successful !');
                $scope.archiveMessageData = data.data;
                $scope.GetCommentsOnTimeline(mess);
            })
            .catch(function (error) {
                console.error('Archive comment failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Get comments on timeline
    ** Method: GET
    */
    $scope.comments = {};
    $scope.GetCommentsOnTimeline = function (message) {
        $rootScope.showLoading();
        Timeline.Comments().get({
            id: message.timelineId,
            token: $rootScope.userDatas.token,
            message: message.id
        }).$promise
            .then(function (data) {
                console.log('Get comments on timeline successful !');
                $scope.comments = data.data.array;
            })
            .catch(function (error) {
                console.error('Get comments on timeline failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    /*
    ** Post comment on message
    ** Method: POST
    */
    $scope.comment = {};
    $scope.PostComment = function (mess) {
        $rootScope.showLoading();
        Timeline.PostMessage().save({
            data: {
                id: mess.timelineId,
                token: $rootScope.userDatas.token,
                title: '',
                message: $scope.comment.message,
                commentedId: mess.id
            }
        }).$promise
            .then(function (data) {
                console.log('Post comment successful !');
                $scope.GetCommentsOnTimeline(mess);
            })
            .catch(function (error) {
                console.error('Post message failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $rootScope.hideLoading();
            })
    }
})