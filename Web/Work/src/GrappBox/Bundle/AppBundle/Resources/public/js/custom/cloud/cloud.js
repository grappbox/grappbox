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
  $scope.button = { hasParent: false, canDelete: false, }
  $scope.path = { current: ',', parent: '', child: '' };
  $scope.selected = { element: '', filename: '', isSecured: '' };
  $scope.newObject = { name: '', isSecured: '' };

  // HTTP callback routines definition
  var cloudObjects_setOnSuccess = function(response) {
    $scope.cloudObjects = (response.data && response.data.data ? (Object.keys(response.data.data.array).length ? response.data.data.array : null) : null);
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

  // Cloud ROOT folder content getter
  var cloud_updateCurrentFolderContent = function() {
    $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + $scope.path.current)
    .then(function successCallback(response) {
      cloudObjects_setOnSuccess(response);
    },
    function errorCallback(response) {
      cloudObjects_setOnError();
    });
  }

  // START
  cloud_updateCurrentFolderContent();

  // Single clic handler (file/folder)
  $scope.cloud_selectObject = function($event) {
    angular.element(document.querySelector('#selected')).removeAttr('id');
    if ($scope.selected.element === $event.currentTarget) {
      $scope.button.canDelete = false;
      selectedObject_reset();
    }
    else {
      $scope.button.canDelete = true;
      angular.element($event.currentTarget).attr('id', 'selected');
      $scope.selected.element = $event.currentTarget;
      $scope.selected.filename = angular.element(document.querySelector('#selected')).attr('data-id');
      $scope.selected.isSecured = angular.element(document.querySelector('#selected')).attr('data-secured');
    }
  };

  // Double clic handler (file/folder)
  $scope.cloud_accessObject = function(object) {
    var local_objectURL = $rootScope.apiBaseURL + '/cloud/file/' + $scope.path.current + ($scope.path.current === ',' ? '' : ',') + object.filename + '/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID;

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
      $scope.path.child = $scope.path.current + ($scope.path.current === ',' ? '' : ',') + object.filename;
      $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + $scope.path.child)
        .then(function successCallback(response) {
          cloudObjects_setOnSuccess(response);
          $scope.path.parent = $scope.path.current;
          $scope.path.current = $scope.path.child;
          $scope.path.child = '';
          $scope.button.hasParent = true;
        },
        function errorCallback(response) {
          Notification.warning({ message: 'Unable to access ' + object.filename + '. Please try again.', delay: 10000 });
      });
    }
  };

  // 'parent' button handler
  $scope.cloud_accessParentObject = function() {
    if ($scope.path.parent) {
      Notification.info({ message: 'Loading...', delay: 2000 });
      $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + ($scope.path.parent === '' ? ',' : $scope.path.parent))
        .then(function successCallback(response) {
          cloudObjects_setOnSuccess(response);
          $scope.path.current = ($scope.path.parent === '' ? ',' : $scope.path.parent);
          $scope.path.parent = $scope.path.current.substring(0, $scope.path.current.lastIndexOf(','));
          $scope.path.child = '';
          $scope.button.hasParent = ($scope.path.current === ',' ? false : true);
          if ($scope.path.parent === '')
            $scope.path.parent = ',';
        },
        function errorCallback(response) {
          Notification.warning({ message: 'Unable to access parent folder. Please try again.', delay: 10000 });
      });
    }
  };

  // 'Delete' button handler
  $scope.cloud_deleteObject = function() {
    if ($scope.selected.filename) {
      if ($scope.selected.filename === "Safe")
        Notification.info({ message: 'You cannot delete the \'Safe\' folder.', delay: 10000 });          
      else {
        Notification.info({ message: 'Loading...', delay: 2000 });
        $http.delete($rootScope.apiBaseURL + '/cloud/file/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + $scope.path.current + ($scope.path.current === ',' ? '' : ',') + $scope.selected.filename)
          .then(function successCallback(response) {
            $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + $scope.path.current)
              .then(function successCallback(response) {
                cloudObjects_setOnSuccess(response);
                Notification.success({ message: 'Deleted: ' + $scope.selected.filename, delay: 10000 });
                $scope.button.canDelete = false;
                selectedObject_reset();
              },
              function errorCallback(response) {
                cloudObjects_setOnError();
              });
          },
          function errorCallback(response) {
            Notification.warning({ message: 'Unable to delete ' + $scope.selected.filename + '. Please try again.', delay: 10000 });
          });
      }
    }
  }

  // 'Upload file' button handler
  $scope.cloud_onUpload = function() {
    if ($scope.cloud_uploadObject) {
      Notification.info({ message: 'Loading...', delay: 2000 });
      var local_fileData = "";
      var local_fileStreamID = "";
      var local_fileDataChunkSent = 0;

      $http.post($rootScope.apiBaseURL + '/cloud/stream/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID, {
        data: {
          filename: $scope.cloud_uploadObject.filename,
          path: $scope.path.current.split(',').join('/'),
          password: ''
        }})
        .then(function successCallback(response) {
          Notification.success({ message: 'Opened stream for ' + $scope.cloud_uploadObject.filename, delay: 10000 });
          local_fileStreamID = response.data.data.stream_id;
          local_fileData = $scope.cloud_uploadObject.base64.match(/.{1,1048576}/g);
          for (var i = 0; i < local_fileData.length; ++i) {
            $http.put($rootScope.apiBaseURL + '/cloud/file', {
              data: {
                token: $cookies.get('USERTOKEN'),
                stream_id: local_fileStreamID,
                project_id: $scope.projectID,
                chunk_numbers: local_fileData.length,
                current_chunk: i,
                file_chunk: local_fileData[i]
              }})
              .then(function successCallback(response) {
                ++local_fileDataChunkSent;
                Notification.info({ message: $scope.cloud_uploadObject.filename + ' sent at ' + parseInt(local_fileDataChunkSent / local_fileData.length * 100) + '%', delay: 10000 });
                if (local_fileDataChunkSent === local_fileData.length) {
                  $http.delete($rootScope.apiBaseURL + '/cloud/stream/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + local_fileStreamID)
                    .then(function successCallback(response) {
                      Notification.success({ message: 'Closed stream for ' + $scope.cloud_uploadObject.filename, delay: 10000 });
                      cloud_updateCurrentFolderContent();                 
                    },
                    function errorCallback(response) {
                      Notification.warning({ message: 'Unable to close stream for ' + $scope.cloud_uploadObject.filename + '. Please try again.', delay: 10000 });  
                    });
                  }
              },
              function errorCallback(response) {
                Notification.warning({ message: 'Chunk #' + i + ' has failed', delay: 10000 });
              });
          };
        },
        function errorCallback(response) {
          Notification.warning({ message: 'Unable to open stream for ' + $scope.cloud_uploadObject.filename + '. Please try again.', delay: 10000 });            
        });
    }
  };

  // 'Add folder' button handler
  $scope.cloud_addNewObject = function() {
    $scope.newObject.name = angular.element(document.querySelector('#cloud_newObjectInput')).val();
    if ($scope.newObject.name) {
      Notification.info({ message: 'Loading...', delay: 2000 });
      $http.post($rootScope.apiBaseURL + '/cloud/createdir', {
        data: {
          token: $cookies.get('USERTOKEN'),
          project_id: $scope.projectID,
          path: $scope.path.current.split(',').join('/'),
          dir_name: $scope.newObject.name
        }})
        .then(function successCallback(response) {
          $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/,' + $scope.path.current)
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