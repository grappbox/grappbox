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
app.controller('cloudController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', '$window', 'Notification', '$uibModal', function($rootScope, $scope, $routeParams, $http, $cookies, $window, Notification, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.project    = { id: $routeParams.id };
  $scope.button     = { parent: false, delete: false };
  $scope.data       = { objects: '', isValid: false };

  $scope.path       = { current: ',', parent: '', child: '' };
  $scope.selected   = { isSecured: '', element: '', name: '' };
  $scope.newFolder  = { isSecured: '', password: '', name: '' };
  $scope.newFile    = { isSecured: '', password: '' };

  // Routine definition
  // Set scope variables with API return data (file/folder list)
  var setDataOnSuccess = function(response) {
    $scope.data.objects = (response.data && response.data.data ? (Object.keys(response.data.data.array).length ? response.data.data.array : null) : null);
    $scope.data.isValid = true;
  }
  var setDataOnFailure = function() {
    $scope.data.objects = null;
    $scope.data.isValid = false;
  }

  // Routine definition
  // Reset scope variable 'selected'
  var resetSelected = function() {
    $scope.selected.isSecured = '';
    $scope.selected.element = '';
    $scope.selected.name = '';
  }

  // Routine definition
  // Toggle scope variable 'button.parent' state
  var toggleParentButton = function(state) {
    $scope.button.parent = state;
  }

  // Routine definition
  // Toggle scope variable 'button.delete' state
  var toggleDeleteButton = function(state) {
    $scope.button.delete = state;
  }

  // Routine definition
  // Get current folder content 
  var getCurrentFolderContent = function() {
    $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + $scope.path.current)
    .then(function successCallback(response) {
      setDataOnSuccess(response);
    },
    function errorCallback(response) {
      setDataOnFailure();
    });
  }


  /* ==================== START ==================== */

  // START
  getCurrentFolderContent();

  // Single clic handler (file/folder)
  $scope.view_selectObject = function($event) {
    angular.element(document.querySelector('#selected')).removeAttr('id');
    if ($scope.selected.element === $event.currentTarget) {
      toggleDeleteButton(false);
      resetSelected();
    }
    else {
      toggleDeleteButton(true);
      angular.element($event.currentTarget).attr('id', 'selected');
      $scope.selected.element = $event.currentTarget;
      $scope.selected.name = angular.element(document.querySelector('#selected')).attr('data-id');
      $scope.selected.isSecured = angular.element(document.querySelector('#selected')).attr('data-secured');
    }
  };

  // Object path format
  $scope.data.formatObjectPath = function(pathToFormat) {
    return ('ROOT').concat((pathToFormat).split(',').join(' / ')) ;
  };

  // Object size format
  $scope.data.formatObjectSize = function(sizeToFormat) {
    return (sizeToFormat ? (sizeToFormat > 100000 ? (sizeToFormat / 1048576).toFixed(2) + ' MB' : (sizeToFormat / 1024).toFixed(2) + ' KB') : 'N/A');
  };


  /* ==================== ACCESS OBJECT (DOWNLOAD FILE/LIST FOLDER CONTENT) ==================== */

  // Double clic handler (file/folder) [1/2]
  // FILE DOWNLOAD
  var local_retrieveSelectedFile = function(selectedFilename) {
    var local_selectedFileURL = $rootScope.apiBaseURL + '/cloud/file/'
    + $scope.path.current + ($scope.path.current === ',' ? '' : ',') + selectedFilename + '/'
    + $cookies.get('USERTOKEN') + '/' + $scope.project.id;

    $http.get(local_selectedFileURL)
    .then(function downloadFileSuccess(response) {
      $window.open(local_selectedFileURL);
      Notification.success({ message: 'Downloaded: ' + selectedFilename, delay: 5000 });
    },
    function downloadFileError(response) {
      Notification.warning({ message: 'Unable to download ' + selectedFilename + '. Please try again.', delay: 5000 });
    });
  };

  // Double clic handler (file/folder) [2/2]
  // FILE DOWNLOAD / FOLDER CONTENT LIST
  $scope.view_accessObject = function(object) {
    Notification.info({ message: 'Loading...', delay: 5000 });
    if (object.type === 'file') {
      local_retrieveSelectedFile(object.filename);
    }
    else if (object.type === 'dir') {
      $scope.path.child = $scope.path.current + ($scope.path.current === ',' ? '' : ',') + object.filename;
      $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + $scope.path.child)
      .then(function getNewFolderContentSuccess(response) {
        setDataOnSuccess(response);
        $scope.path.parent = $scope.path.current;
        $scope.path.current = $scope.path.child;
        $scope.path.child = '';
        toggleParentButton(true);
      },
      function getNewFolderContentError(response) {
        Notification.warning({ message: 'Unable to access ' + object.filename + '. Please try again.', delay: 5000 });
      });
    }
  };

  // 'Parent' button handler
  // PREVIOUS FOLDER CONTENT LIST
  $scope.view_accessParentObject = function() {
    if ($scope.path.parent && $scope.button.parent) {
      Notification.info({ message: 'Loading...', delay: 5000 });
      $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + ($scope.path.parent === '' ? ',' : $scope.path.parent))
      .then(function successCallback(response) {
        setDataOnSuccess(response);
        $scope.path.current = ($scope.path.parent === '' ? ',' : $scope.path.parent);
        $scope.path.parent = $scope.path.current.substring(0, $scope.path.current.lastIndexOf(','));
        $scope.path.child = '';
        toggleParentButton($scope.path.current === ',' ? false : true);
        if ($scope.path.parent === '')
          $scope.path.parent = ',';
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to access parent folder. Please try again.', delay: 5000 });
      });
    }
  };


  /* ==================== DELETE OBJECT (FILE/FOLDER) ==================== */

  // 'Delete' button handler
  $scope.view_deleteObject = function() {
    if ($scope.selected.name) {
      if ($scope.selected.name === "Safe")
        Notification.info({ message: 'You cannot delete the \'Safe\' folder.', delay: 5000 });          
      else {
        Notification.info({ message: 'Loading...', delay: 5000 });
        $http.delete($rootScope.apiBaseURL + '/cloud/file/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + $scope.path.current + ($scope.path.current === ',' ? '' : ',') + $scope.selected.name)
        .then(function successCallback(response) {
          $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + $scope.path.current)
          .then(function successCallback(response) {
            Notification.success({ message: 'Deleted: ' + $scope.selected.name, delay: 5000 });
            setDataOnSuccess(response);
            toggleDeleteButton();
            resetSelected();
          },
          function errorCallback(response) {
            setDataOnFailure();
          });
        },
        function errorCallback(response) {
          Notification.warning({ message: 'Unable to delete ' + $scope.selected.name + '. Please try again.', delay: 5000 });
        });
      }
    }
  };


  /* ==================== CREATE OBJECT (UPLOAD FILE) ==================== */

  // 'Upload file' button handler [1/2]
  var local_uploadSelectedFile = function() {
    var local_fileData = "";
    var local_fileStreamID = "";
    var local_fileDataChunkSent = 0;

    Notification.info({ message: 'Loading...', delay: 5000 });
    $http.post($rootScope.apiBaseURL + '/cloud/stream/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id, {
      data: {
        filename: $scope.view_newFile.filename,
        path: $scope.path.current.split(',').join('/'),
        password: ($scope.newFile.isSecured ? $scope.newFile.password : '')
        /*password: ($scope.newFile.isSecured ? sha512($scope.newFile.password) : '')*/
        /*safe_password:*/
      }})
    .then(function streamOpeningSuccess(response) {
      Notification.info({ message: 'Sending ' + $scope.view_newFile.filename + '...', delay: 5000 });
      local_fileStreamID = response.data.data.stream_id;
      local_fileData = $scope.view_newFile.base64.match(/.{1,1048576}/g);
      for (var i = 0; i < local_fileData.length; ++i) {
        $http.put($rootScope.apiBaseURL + '/cloud/file', {
          data: {
            token: $cookies.get('USERTOKEN'),
            stream_id: local_fileStreamID,
            project_id: $scope.project.id,
            chunk_numbers: local_fileData.length,
            current_chunk: i,
            file_chunk: local_fileData[i]
          }})
        .then(function chunkSendingSuccess(response) {
          ++local_fileDataChunkSent;
          Notification.info({ message: $scope.view_newFile.filename + ' sent at ' + parseInt(local_fileDataChunkSent / local_fileData.length * 100) + '%', delay: 5000 });
          if (local_fileDataChunkSent === local_fileData.length) {
            $http.delete($rootScope.apiBaseURL + '/cloud/stream/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/' + local_fileStreamID)
            .then(function streamClosingSuccess(response) {
              Notification.success({ message: 'Sent: ' + $scope.view_newFile.filename, delay: 5000 });
              getCurrentFolderContent();
            },
            function streamClosingFailure(response) {
              Notification.warning({ message: 'Unable to close stream for ' + $scope.view_newFile.filename + '. Please try again.', delay: 5000 });  
            });
          }
        },
        function chunkSendingFailure(response) {
          Notification.warning({ message: 'Chunk #' + i + ' has failed', delay: 5000 });
        });
      }
    },
    function streamOpeningFailure(response) {
      Notification.warning({ message: 'Unable to open stream for ' + $scope.view_newFile.filename + '. Please try again.', delay: 5000 });
    });
  }

  // 'Upload file' button handler [2/2]
  $scope.view_onNewFile = function() {
    if ($scope.view_newFile) {
      $scope.newFile.isSecured = false;
      $scope.newFile.password = '';

      var local_passwordConfirmationModalInstance = $uibModal.open({
        animation: true,
        templateUrl: 'view_isNewFileProtected.html',
        controller: 'view_isNewFileProtected',
      });
      local_passwordConfirmationModalInstance.result.then(function fileWillHavePassword() {
        var local_operateOnObjectModalInstance = $uibModal.open({
          animation: true,
          templateUrl: 'view_setNewFileFirstPassword.html',
          controller: 'view_setNewFileFirstPassword',
        });
        local_operateOnObjectModalInstance.result.then(function fileFirstPasswordEntered(data) {
          var local_operateOnObjectModalInstanceAgain = $uibModal.open({
            animation: true,
            templateUrl: 'view_setNewFileSecondPassword.html',
            controller: 'view_setNewFileSecondPassword',
          });
          $scope.newFile.password = data;
          local_operateOnObjectModalInstanceAgain.result.then(function fileSecondPasswordEntered(data) {
            if ($scope.newFile.password === data) {
              $scope.newFile.isSecured = true;
              local_uploadSelectedFile();
            }
            else {
              $scope.newFile.isSecured = false;
              $scope.newFile.password = '';
              Notification.warning({ message: 'The two passwords are differents.', delay: 5000 });
            }
          },
          function fileSecondPasswordNotEntered() {
            Notification.warning({ message: 'You need to provide your password two times to ensure its correctness.', delay: 5000 });
            Notification.warning({ message: 'Upload cancelled.', delay: 5000 });
          })
        },
        function fileFirstPasswordNotEntered() {
          Notification.warning({ message: 'Upload cancelled.', delay: 5000 });
        })
      },
      function fileWontHavePassword() {
        $scope.newFile.isSecured = false;
        local_uploadSelectedFile();
      })
    }
  };


  /* ==================== CREATE OBJECT (CREATE FOLDER) ==================== */

  // 'Add folder' button handler
  $scope.view_onNewFolder = function() {
    $scope.newFolder.name = angular.element(document.querySelector('#view_newFolder')).val();
    if ($scope.newFolder.name) {
      Notification.info({ message: 'Loading...', delay: 5000 });
      $http.post($rootScope.apiBaseURL + '/cloud/createdir', {
        data: {
          token: $cookies.get('USERTOKEN'),
          project_id: $scope.project.id,
          path: $scope.path.current.split(',').join('/'),
          dir_name: $scope.newFolder.name
        }})
      .then(function successCallback(response) {
        $http.get($rootScope.apiBaseURL + '/cloud/list/' + $cookies.get('USERTOKEN') + '/' + $scope.project.id + '/,' + $scope.path.current)
        .then(function successCallback(response) {
          setDataOnSuccess(response);
          Notification.success({ message: 'Created: ' + $scope.newFolder.name, delay: 5000 });
          $scope.newFolder.name = '';
          $scope.newFolder.isSecured = '';
          angular.element(document.querySelector('#view_newFolder')).val('');
        },
        function errorCallback(response) {
          setDataOnFailure();
        });
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to create ' + $scope.newFolder.name + '. Please try again.', delay: 5000 });
      });
    }
    else
      Notification.warning({ message: 'You must provide a valid folder name. Please try again.', delay: 5000 });
  }

}]);



/**
* Controller definition (from view)
* FILE UPLOAD => is new file will be protected?
*
*/
app.controller('view_isNewFileProtected', ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
  $scope.view_newFileIsProtected = function() {
    $uibModalInstance.close();
  };
  $scope.view_newFileIsNotProtected = function() {
    $uibModalInstance.dismiss();
  };
}]);



/**
* Controller definition
* FILE UPLOAD => set first password for new file.
*
*/
app.controller('view_setNewFileFirstPassword', ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
  $scope.view_newFileFirstPasswordSuccess = function() {
    var data = angular.element(document.querySelector('#view_newFileFirstPassword'));
    angular.element(data).removeAttr('class');
    if (data && data.val() !== '')
      $uibModalInstance.close(data.val());
    else
      angular.element(data).attr('class', 'input-error');
  };
  $scope.view_newFileFirstPasswordFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



/**
* Controller definition
* FILE UPLOAD => set second password for new file (verification).
*
*/
app.controller('view_setNewFileSecondPassword', ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
  $scope.view_newFileSecondPasswordSuccess = function() {
    var data = angular.element(document.querySelector('#view_newFileSecondPassword'));
    angular.element(data).removeAttr('class');
    if (data && data.val() !== '')
      $uibModalInstance.close(data.val());
    else
      angular.element(data).attr('class', 'input-error');
  };
  $scope.view_newFileSecondPasswordFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



/**
* Controller definition
* FILE DOWNLOAD => get password for file.
*
*/
app.controller('view_filePasswordCheck', ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
  $scope.view_filePasswordCheckSuccess = function() {
    $uibModalInstance.close(angular.element(document.querySelector('#view_filePassword')).val());
  };
  $scope.view_filePasswordCheckFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



/**
* Controller definition
* SAFE ACCESS => get password for 'Safe' folder.
*
*/
app.controller('cloud_safePasswordCheck', ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
  $scope.view_safePasswordCheckSuccess = function() {
    $uibModalInstance.close(angular.element(document.querySelector('#view_safePassword')).val());
  };
  $scope.view_safePasswordCheckFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);