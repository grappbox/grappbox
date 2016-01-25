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

  // Scope variables initialization
  $scope.projectID = $routeParams.id;
  $scope.button = { previous: false, delete: false, }
  $scope.path = { current: ',', old: '', new: '' };
  $scope.selected = { element: '', filename: '', isSecured: '' };
  $scope.newObject = { name: '', isSecured: '' };

  // HTTP callback routines definition
  var cloudObjects_setOnSuccess = function(response) {
    $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data).length ? response.data.data.array : null) : null);
    $scope.cloudObjects_isValid = true;
  }
  var cloudObjects_setOnError = function() {
    $scope.cloudObjects = null;
    $scope.cloudObjects_isValid = false;
  }

  // Scope array 'selectedObject' routine definition
  var selectedObject_reset = function() {
    $scope.selected.element = '';
    $scope.selected.filename = '';
    $scope.selected.isSecured = '';
  }

  // START
  // Cloud root folder content get
  $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current)
    .then(function successCallback(response) {
      cloudObjects_setOnSuccess(response);
    },
    function errorCallback(response) {
      cloudObjects_setOnError();
    });

    // Single clic handler (file/folder)
    $scope.cloud_selectObject = function($event, object) {
      angular.element(document.querySelector('#selected')).removeAttr('id');
      if ($scope.selected.element === $event.currentTarget) {
        $scope.button.delete = false;
        selectedObject_reset();
      }
      else {
        $scope.button.delete = true;
        angular.element($event.currentTarget).attr('id', 'selected');
        $scope.selected.element = $event.currentTarget;
        $scope.selected.filename = angular.element(document.querySelector('#selected')).attr('data-id');
        $scope.selected.isSecured = angular.element(document.querySelector('#selected')).attr('data-secured');
      }
    };

    // Double clic handler (file/folder)
    $scope.cloud_accessObject = function(object) {
      var local_objectURL = $rootScope.apiBaseURL + '/cloud/file/' + $scope.path.current + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id;

      Notification.info({ message: 'Loading...', delay: 2000 });
      if (object.type === 'file') {
        $http.get(local_objectURL)
          .then(function successCallback(response) {
            $window.open(local_objectURL);
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
            cloudObjects_setOnSuccess(response);
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
            cloudObjects_setOnSuccess(response);
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
          Notification.info({ message: 'You cannot delete the \'Safe\' folder.', delay: 10000 });          
        else {
          Notification.info({ message: 'Loading...', delay: 2000 });
          $http.delete($rootScope.apiBaseURL + '/cloud/file/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current + $scope.selected.filename)
            .then(function successCallback(response) {
              $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current)
                .then(function successCallback(response) {
                  cloudObjects_setOnSuccess(response);
                  Notification.success({ message: 'Deleted: ' + $scope.selected.filename, delay: 10000 });
                  $scope.button.delete = false;
                  selectedObject_reset();
                },
                function errorCallback(response) {
                  cloudObjects_setOnError();
                });
            },
            function errorCallback(response) {
              Notification.warning({ message: 'Unable to delete ' + object.filename + '. Please try again.', delay: 10000 });
            });
        }
      }
    }

    // 'Add folder' button handler
    $scope.cloud_addNewObject = function(object) {
      $scope.newObject.name = angular.element(document.querySelector('#cloud_newObjectInput')).val();
      if ($scope.newObject.name) {
        Notification.info({ message: 'Loading...' + $scope.newObject.name, delay: 2000 });
        $http.post($rootScope.apiBaseURL + '/cloud/createdir', {
          data: {
            token: $cookies.get('USERTOKEN'),
            project_id: $routeParams.id,
            path: $scope.path.current,
            dir_name: $scope.newObject.name
          }})
          .then(function successCallback(response) {
            $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $routeParams.id + '/' + $scope.path.current)
              .then(function successCallback(response) {
                cloudObjects_setOnSuccess(response);
                Notification.success({ message: 'Created: ' + $scope.newObject.name, delay: 10000 });
                $scope.newObject.name = '';
                $scope.newObject.isSecured = '';
                angular.element(document.querySelector('#cloud_newObjectInput')).text();
              },
              function errorCallback(response) {
                cloudObjects_setOnError();
              });
          },
          function errorCallback(response) {
            Notification.warning({ message: 'Unable to create ' + $scope.newObject.name + '. Please try again.', delay: 10000 });
          });
      }
      else
        Notification.warning({ message: 'You must provide a valid folder name. Please try again.', delay: 10000 });
    }

}]);