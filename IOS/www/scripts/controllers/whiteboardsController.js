/*
    Summary: WHITEBOARD LIST Controller
*/

angular.module('GrappBox.controllers')

// WHITEBOARD LIST
.controller('WhiteboardsCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, Toast, Whiteboard) {

    // UNCOMMENT AFTER REDO THE PROJECT SELECTION BEFORE DASHBOARD
    $scope.projectId = $stateParams.projectId;

    //Refresher
    $scope.doRefresh = function () {
        $scope.ListWhiteboards();
        console.log("View refreshed !");
    }

    /*
    ** List all whiteboards
    ** Method: GET
    */
    $scope.whiteboardsTab = {};
    $scope.ListWhiteboards = function () {
        //$rootScope.showLoading();
        Whiteboard.List().get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('List whiteboards successful !');
                console.log(data.data.array);
                if (data.data.array.length == 0)
                    $scope.noWhiteboard = "There is no whiteboard.";
                else
                    $scope.noWhiteboard = false;
                $scope.whiteboardsTab = data.data.array;
            })
            .catch(function (error) {
                console.error('List whiteboards failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.ListWhiteboards();

    /*
    ** Create a new whiteboard
    ** Method: POST
    */
    $scope.createWhiteboardData = {};
    $scope.CreateWhiteboard = function () {
        //$rootScope.showLoading();
        Whiteboard.Create().save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                whiteboardName: $scope.whiteboardName.name
            }
        }).$promise
            .then(function (data) {
                console.log('Create whiteboard successful !');
                Toast.show("Whiteboard created");
                $scope.noWhiteboard = false;
                console.log(data.data);
                $scope.createWhiteboardData = data.data;
            })
            .catch(function (error) {
                console.error('Create whiteboard failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Whiteboard creation error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                $scope.ListWhiteboards();
            })
    }

    $scope.whiteboardName = {};
    // Enter whiteboard name popup
    $scope.showNameWhiteboardPopup = function () {
        var myPopup = $ionicPopup.show({
            template: '<input type="text" placeholder="Name for whiteboard" ng-model="whiteboardName.name">',
            title: 'Choose Name',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.whiteboardName.name) {
                          // Don't allow the user to close unless he enters file password
                          e.preventDefault();
                      } else {
                          return $scope.whiteboardName;
                      }
                  }
              }]
        })
        .then(function (res) {
            if (res && res.name) {
                if ($scope.whiteboardName.name != 'undefined')
                    $scope.CreateWhiteboard();
            }
        });
    };

    /*
    ** Delete a whiteboard
    ** Method: DELETE
    */
    $scope.DeleteWhiteboard = function (whiteboard) {
        //$rootScope.showLoading();
        Whiteboard.Delete().delete({
            token: $rootScope.userDatas.token,
            id: whiteboard.id
        }).$promise
            .then(function (data) {
                console.log('Delete whiteboard successful !');
                Toast.show("Whiteboard deleted");
                console.log(data.info);
            })
            .catch(function (error) {
                console.error('Delete whiteboard failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Whiteboard deletion error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                $scope.ListWhiteboards();
            })
    }
})