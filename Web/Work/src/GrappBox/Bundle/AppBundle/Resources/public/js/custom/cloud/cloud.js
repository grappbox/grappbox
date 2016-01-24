/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP Cloud page content
*
*/
app.controller('cloudController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', '$window', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, $window, Notification) {
  $scope.currentProjectID = $routeParams.id;

  $scope.button = { previous: false, delete: false, }
  $scope.path = { current: ',', old: '', new: '' };
  $scope.selected = { element: '', filename: '', isSecured: '' };

  // Cloud default root path
  $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current)
    .then(function successCallback(response) {
      $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data).length ? response.data.data.array : null) : null);
      $scope.cloudObjects_isValid = true;
    },
    function errorCallback(response) {
      $scope.cloudObjects = null;
      $scope.cloudObjects_isValid = false;
    });

    // Single clic handler (file/folder)
    $scope.cloud_selectObject = function($event, object) {
      angular.element(document.querySelector('#selected')).removeAttr('id');
      if ($scope.selected.element === $event.currentTarget) {
        $scope.selected.element = '';
        $scope.selected.filename = '';
        $scope.selected.isSecured = '';
        $scope.button.delete = false;
      }
      else {
        angular.element($event.currentTarget).attr('id', 'selected');
        $scope.selected.element = $event.currentTarget;
        $scope.selected.filename = angular.element(document.querySelector('#selected')).attr('data-id');
        $scope.selected.isSecured = angular.element(document.querySelector('#selected')).attr('data-secured');
        $scope.button.delete = true;
      }
    };

    // Double clic handler (file/folder)
    $scope.cloud_accessObject = function(object) {
      Notification.info({ message: 'Loading...', delay: 2000 });
      if (object.type === 'file') {
        $http.get($rootScope.apiBaseURL + '/cloud/file/' + $scope.path.current + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id)
          .then(function successCallback(response) {
            $window.open($rootScope.apiBaseURL + '/cloud/file/' + $scope.path.current + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id);
            Notification.success({ message: 'Downloaded: ' + object.filename, delay: 10000 });
        },
        function errorCallback(response) {
          Notification.warning({ message: 'Unable to download ' + object.filename + '. Please try again.', delay: 10000 });
        });
      }
      else if (object.type === 'dir') {
        $scope.path.new = $scope.path.current + ($scope.path.current === ',' ? '' : '/') + object.filename;
        $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.new)
          .then(function successCallback(response) {
            $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data).length ? response.data.data.array : null) : null);
            $scope.cloudObjects_isValid = true;
            $scope.path.old = $scope.path.current;
            $scope.path.current = $scope.path.new + '/';

            $scope.button.previous = true;
          },
          function errorCallback(response) {
            Notification.warning({ message: 'Unable to access ' + object.filename + '. Please try again.', delay: 10000 });
        });
      }
    };

    // 'Previous' button handler
    $scope.cloud_accessPreviousObject = function(object) {
      if ($scope.path.old) {
        Notification.info({ message: 'Loading...', delay: 2000 });
        $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.old)
          .then(function successCallback(response) {
            $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data).length ? response.data.data.array : null) : null);
            $scope.cloudObjects_isValid = true;
            $scope.path.new = $scope.path.current;
            $scope.path.current = $scope.path.old;

            $scope.button.previous = false;
          },
          function errorCallback(response) {
            Notification.warning({ message: 'Unable to access previous folder. Please try again.', delay: 10000 });
        });
      }
    };

    // 'Delete' button handler
    $scope.cloud_deleteObject = function(object) {
      if ($scope.selected.filename) {
        if ($scope.selected.filename === "Safe")
          Notification.info({ message: 'You can\'t delete the \'Safe\' folder.', delay: 10000 });          
        else {
          Notification.info({ message: 'Loading...', delay: 2000 });
          $http.delete($rootScope.apiBaseURL + '/cloud/file/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current + $scope.selected.filename)
            .then(function successCallback(response) {
              $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current)
                .then(function successCallback(response) {
                  $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data).length ? response.data.data.array : null) : null);
                  $scope.cloudObjects_isValid = true;
                  Notification.success({ message: 'Deleted: ' + $scope.selected.filename, delay: 10000 });
                  $scope.selected.element = '';
                  $scope.selected.filename = '';
                  $scope.selected.isSecured = '';
                  $scope.button.delete = false;
                },
                function errorCallback(response) {
                  $scope.cloudObjects = null;
                  $scope.cloudObjects_isValid = false;
                });
            },
            function errorCallback(response) {
              Notification.warning({ message: 'Unable to delete ' + object.filename + '. Please try again.', delay: 10000 });
            });
        }
      }
    }

}]);