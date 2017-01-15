/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP cloud
app.controller("CloudController", ["accessFactory", "$rootScope", "$scope", "$routeParams", "$http", "localStorageService", "$base64", "$window", "notificationFactory", "$uibModal", "$q",
    function(accessFactory, $rootScope, $scope, $routeParams, $http, localStorageService, $base64, $window, notificationFactory, $uibModal, $q) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.project    = { id: $routeParams.project_id };
  $scope.button     = { parent: false, delete: false };
  $scope.data       = { loaded: false, valid: false, authorized: "", objects: "" };

  $scope.filePath   = { current: ",", parent: "", child: "" };
  $scope.selected   = { current: { isSecured: "", name: "" }, previous: { isSecured: "", name: "" }, password: "", error: "" };
  $scope.newFolder  = { isSecured: "", name: "", error: "" };
  $scope.newFile    = { isSecured: "", password: "", password_confirmation: "", error: "", input: "" };
  $scope.safe       = { password: "", isSet: false, error: "" };

  // Routine definition
  // Set scope variables with API return data (file/folder list)
  var setDataOnSuccess = function(response) {
    $scope.data.objects     = (response.data && response.data.data ? (Object.keys(response.data.data.array).length ? response.data.data.array : null) : null);
    $scope.data.valid       = true;
    $scope.data.loaded      = true;
  };

  // Routine definition
  // Set scope variables with load fail message (file/folder list)
  var setDataOnFailure = function() {
    $scope.data.objects     = null;
    $scope.data.valid       = false;
    $scope.data.loaded      = true;
  };

  // Routine definition
  // Reset scope variable "selected"
  var resetSelected = function() {
    $scope.selected.previous.isSecured  = $scope.selected.current.isSecured;
    $scope.selected.previous.name       = $scope.selected.current.name;
    $scope.selected.current.isSecured   = "";
    $scope.selected.current.name        = "";
    $scope.selected.password            = "";
  };

  // Routine definition
  // Toggle scope variable "button.parent" state
  var toggleParentButton = function(state) {
    $scope.button.parent = state;
  };

  // Routine definition
  // Toggle scope variable "button.delete" state
  var toggleDeleteButton = function(state) {
    $scope.button.delete = state;
  };






  /* ==================== SAFE PASSWORD (ROUTINES) ==================== */

  // Routine definition
  // Check if given path is located inside the "Safe" folder
  var isPathInSafeFolder = function(path) {
    return (path.indexOf(",Safe") == 0);
  };

  // Routine definition
  // Get "Safe" folder password
  var getSafePassword = function() {
    var modalInstance_getSafePassword = "";
    var deferred                      = "";

    deferred = $q.defer();
    if (localStorageService.get("cloud.safe")) {
      $scope.safe.password = $base64.decode(localStorageService.get("cloud.safe"));
      deferred.resolve();
    }
    else {
      modalInstance_getSafePassword = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_safePasswordCheck.html", controller: "view_safePasswordCheck" });
      modalInstance_getSafePassword.result.then(
        function safePasswordProvided() {
          deferred.resolve();
        },
        function safePasswordNotProvided() {
          deferred.reject();
        }
      );
    }
    return deferred.promise;
  };

  // Routine definition
  // Store "Safe" folder password for current session
  var storeSafePassword = function() {
    if ($scope.safe.password) {
      if (!$scope.safe.isSet)
        notificationFactory.success("\"Safe\" password saved for this session.");
      localStorageService.set("cloud.safe", $base64.encode($scope.safe.password));
      $scope.safe.isSet = true;
    }
  };

  // Routine definition
  // Remove "Safe" folder password from current session storage
  var resetSafePassword = function() {
    localStorageService.remove("cloud.safe");
    $scope.safe.isSet = false;
  };






  /* ==================== START ==================== */

  // Routine definition
  // Get Cloud rights for current user 
  var getRightsOnCloud = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/role/user/part/" + $rootScope.user.id + "/" + $scope.project.id + "/cloud", { headers: { 'Authorization': $rootScope.user.token }}).then(
      function rightsReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          if (response.data.info.return_code == "1.13.1") {
            $scope.data.authorized = response.data.data.value;
            if ($scope.data.authorized == "0")
              deferred.reject();
            else
              deferred.resolve();
          }
        }
        else
          $rootScope.reject(true);
      },
      function rightsNotReceived(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          if (response.data.info.return_code == "13.11.3")
            $rootScope.reject();
        else
          $rootScope.reject(true);
      }
      else
        $rootScope.reject(true);
      deferred.reject();
      }
    );
    return deferred.promise;
  };    

  // Routine definition
  // Get current folder content 
  var getCurrentFolderContent = function() {
    var isCurrentFolderInSafeFolder = "";
    var promise                     = "";

    isCurrentFolderInSafeFolder = isPathInSafeFolder($scope.filePath.current);
    promise = (isCurrentFolderInSafeFolder ? getSafePassword() : $q.when(true));
    promise.then(
      function safeCheckSuccess() {
        $http.get($rootScope.api.url + "/cloud/list/" + $scope.project.id + "/" + $scope.filePath.current + (isPathInSafeFolder($scope.filePath.current) ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
          function folderContentReceived(response) {
            if (response.data.info && response.data.info.return_code == "1.3.1") {
              setDataOnSuccess(response);
              storeSafePassword();
            }
            else {
              notificationFactory.warning("Unable to refresh current folder. Please try again.");
              setDataOnFailure();
              resetSafePassword();
            }
          },
          function folderContentNotReceived() {
            notificationFactory.warning("Unable to refresh current folder. Please try again.");
            setDataOnFailure();
            resetSafePassword();
          }
        );
      },
      function safeCheckFailure() {
        $scope.data.loaded = true;
        notificationFactory.warning("You must provide the \"Safe\" password in order to access any \"Safe\"-based file or folder.");
      }
    );
  };

  // START
  var getRightsOnCloud = getRightsOnCloud();
  getRightsOnCloud.then(
    function userHasRights() {
      getCurrentFolderContent();  
    },
    function userHasNoRights() {
      $scope.data.valid = true;
      $scope.data.loaded = true;
    }
  );

  // Single clic handler (file/folder)
  $scope.view_selectObject = function(object) {
    if ($scope.selected.current.name === object.filename) {
      toggleDeleteButton(false);
      resetSelected();
    }
    else {
      toggleDeleteButton(true);
      $scope.selected.current.name = object.filename;
      $scope.selected.current.isSecured = object.is_secured;
    }
  };

  // Object path format
  $scope.data.formatObjectPath = function(pathToFormat) {
    return ("cloud:/").concat((pathToFormat).split(",").join("/"));
  };

  // Object (last-modified) date format
  $scope.data.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  // Object size format
  $scope.data.formatObjectSize = function(sizeToFormat) {
    return (sizeToFormat ? (sizeToFormat > 1000000 ? (sizeToFormat / 1048576).toFixed(2) + " MB" : (sizeToFormat / 1024).toFixed(2) + " KB") : "N/A");
  };






  /* ==================== ACCESS OBJECT (DOWNLOAD FILE/LIST FOLDER CONTENT) ==================== */

  // Load order (for file)   : view_accessObject() =>  local_accessFile()    => local_retrieveSelectedFile();
  // Load order (for folder) : view_accessObject() =>  local_accessFolder();

  // Double clic handler (file/folder)
  // FILE DOWNLOAD [3/3]
  var local_retrieveSelectedFile = function(selectedFilename, isFileInSafeFolder) {
    var selectedFileURL = "";

    selectedFileURL = $rootScope.api.url + "/cloud/" + ($scope.selected.previous.isSecured ? "filesecured/" : "file/") + $scope.filePath.current + ($scope.filePath.current === "," ? "" : ",") + selectedFilename + "/" + $scope.project.id + ($scope.selected.previous.isSecured ? "/" + $scope.selected.password : "") + (isFileInSafeFolder ? "/" + $scope.safe.password : "");
    $http.get(selectedFileURL, { headers: { "Authorization": $rootScope.user.token }}).then(
      function downloadFileSuccess(response) {
        if (response.data && response.data.info && response.data.info.return_code == "11.1.3")
          $rootScope.reject();
        if (response.data && response.data.location) {
          storeSafePassword();
          $scope.selected.password = "";
          notificationFactory.success("Downloaded: " + selectedFilename);
          $window.open(response.data.location);
        }
        else {
          notificationFactory.warning("Unable to download \"" + selectedFilename + "\". Please try again.");
          resetSafePassword();          
        }
      },
      function downloadFileFailure() {
        notificationFactory.warning("Unable to download \"" + selectedFilename + "\". Please try again.");
        resetSafePassword();
      }
    );
  };

  // Double clic handler (file/folder)
  // FILE DOWNLOAD [2/3]
  var local_accessFile = function(object) {
    var modalInstance_askSelectedFilePassword = "";
    var isFileInSafeFolder                    = "";
    var promise                               = "";

    notificationFactory.loading();
    $scope.filePath.child = $scope.filePath.current + ($scope.filePath.current === "," ? "" : ",") + object.filename;
    isFileInSafeFolder = isPathInSafeFolder($scope.filePath.child);
    promise = (isFileInSafeFolder ? getSafePassword() : $q.when(true));
    promise.then(
      function safeCheckSuccess() {
        if ($scope.selected.previous.isSecured) {
          modalInstance_askSelectedFilePassword = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_filePasswordCheck.html", controller: "view_filePasswordCheck" });
          modalInstance_askSelectedFilePassword.result.then(
            function passwordReceived() {
              local_retrieveSelectedFile(object.filename, isFileInSafeFolder);
            },
            function passwordNotReceived() {
              notificationFactory.warning("You must provide a password in order to access \"" + $scope.selected.previous.name + "\". Please try again.");
            }
          );
        }
        else
          local_retrieveSelectedFile(object.filename, isFileInSafeFolder);
      },
      function safeCheckFailure() {
        notificationFactory.warning("You must provide the \"Safe\" password in order to access any \"Safe\"-based file or folder. Please try again.");
      }
    );
  };

  // Double clic handler (file/folder)
  // FILE DOWNLOAD [1/3]
  // FOLDER ACCESS [1/2]
  $scope.view_accessObject = function(object) {
    if (object.type === "file")
      local_accessFile(object);
    else if (object.type === "dir")
      local_accessFolder(object);
  };

  // Double clic handler (file/folder)
  // FOLDER ACCESS [2/2]
  var local_accessFolder = function(object) {
    var isFolderInSafeFolder  = "";
    var promise               = "";

    notificationFactory.loading();
    $scope.filePath.child = $scope.filePath.current + ($scope.filePath.current === "," ? "" : ",") + object.filename;
    isFolderInSafeFolder = isPathInSafeFolder($scope.filePath.child);
    promise = (isFolderInSafeFolder ? getSafePassword() : $q.when(true));
    promise.then(
      function safeCheckSuccess() {
        $http.get($rootScope.api.url + "/cloud/list/" + $scope.project.id + "/" + $scope.filePath.child + (isFolderInSafeFolder ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
          function folderContentReceived(response) {
            if (response.data.info && response.data.info.return_code == "1.3.1") {
              setDataOnSuccess(response);
              storeSafePassword();
              $scope.filePath.parent = $scope.filePath.current;
              $scope.filePath.current = $scope.filePath.child;
              $scope.filePath.child = "";
              toggleParentButton(true);
            }
            else {
              notificationFactory.warning("Unable to access " + object.filename + ". Please try again.");
              resetSafePassword();
            }
          },
          function folderContentNotReceived() {
            notificationFactory.warning("Unable to access " + object.filename + ". Please try again.");
          }
        );
      },
      function safeCheckFailure() {
        notificationFactory.warning("You must provide the \"Safe\" password in order to access any \"Safe\"-based file or folder. Please try again.");
      }
    );
  };



  /* ==================== ACCESS PARENT OBJECT (FOLDER) ==================== */

  // "Parent" button handler
  $scope.view_accessParentObject = function() {
    var isParentInSafeFolder  = "";
    var promise               = "";

    if ($scope.filePath.parent && $scope.button.parent) {
      notificationFactory.loading();
      isParentInSafeFolder = isPathInSafeFolder($scope.filePath.parent);
      promise = (isParentInSafeFolder ? getSafePassword() : $q.when(true) );
      promise.then(
        function safeCheckSuccess() {
          $http.get($rootScope.api.url + "/cloud/list/" + $scope.project.id + "/" + ($scope.filePath.parent === "" ? "," : $scope.filePath.parent) + (isParentInSafeFolder ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
            function parentfolderContentReceived(response) {
              if (response.data.info && response.data.info.return_code == "1.3.1") {
                setDataOnSuccess(response);
                storeSafePassword();
                $scope.filePath.current = ($scope.filePath.parent === "" ? "," : $scope.filePath.parent);
                $scope.filePath.parent = $scope.filePath.current.substring(0, $scope.filePath.current.lastIndexOf(","));
                $scope.filePath.parent = ($scope.filePath.parent === "" ? "," : $scope.filePath.parent);
                $scope.filePath.child = "";
                toggleParentButton($scope.filePath.current === "," ? false : true);
              }
              else {
                notificationFactory.warning("Unable to access parent folder. Please try again.");
                resetSafePassword();
              }
            },
            function parentfolderContentNotReceived() {
              notificationFactory.warning("Unable to access parent folder. Please try again.");
            }
          );
        },
        function safeCheckFailure() {
          notificationFactory.warning("You must provide the \"Safe\" password in order to access any \"Safe\"-based file or folder. Please try again.");
        }
      );
    }
  };



  /* ==================== DELETE OBJECT (FILE/FOLDER) ==================== */

  // "Delete" button handler (file/folder) [2/2]
  var local_deleteSelectedObject = function(isObjectInSafeFolder) {
    notificationFactory.loading();

    $http.delete($rootScope.api.url + "/cloud/" + ($scope.selected.current.isSecured ? "filesecured/" : "file/") + $scope.project.id + "/" + $scope.filePath.current + ($scope.filePath.current === "," ? "" : ",") + $scope.selected.current.name + ($scope.selected.current.isSecured ? "/" + $scope.selected.password : "") + (isObjectInSafeFolder ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
      function objectDeletionSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.3.1") {
          $http.get($rootScope.api.url + "/cloud/list/" + $scope.project.id + "/" + $scope.filePath.current + (isObjectInSafeFolder ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
            function folderContentReceived(response) {
              notificationFactory.success("Deleted: \"" + $scope.selected.current.name + "\"");
              setDataOnSuccess(response);
              storeSafePassword();
              toggleDeleteButton(false);
              resetSelected();
            },
            function folderContentNotReceived(response) {
              setDataOnFailure();
            }
          );
        }
        else {
          notificationFactory.warning("Unable to delete " + $scope.selected.current.name + ". Please try again.");
          resetSafePassword();
        }
      }
    ),
    function objectDeletionFailure(response) {
      notificationFactory.warning("Unable to delete \"" + $scope.selected.current.name + "\". Please try again.");
    }
  };

  // "Delete" button handler (file/folder) [1/2]
  $scope.view_deleteObject = function() {
    var modalInstance_askSelectedOjectedPassword  = "";
    var isObjectInSafeFolder                      = "";
    var objectPath                                = "";
    var promise                                   = "";

    if ($scope.selected.current.name) {
      if ($scope.selected.current.name === "Safe")
        notificationFactory.info("You cannot delete the \"Safe\" folder.");          
      else {
        objectPath = $scope.filePath.current + ($scope.filePath.current === "," ? "" : ",") + $scope.selected.current.name;
        isObjectInSafeFolder = isPathInSafeFolder(objectPath);
        promise = (isObjectInSafeFolder ? getSafePassword() : $q.when(true) );
        promise.then(
          function safeCheckSuccess() {
            if ($scope.selected.current.isSecured) {
              modalInstance_askSelectedOjectedPassword = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_filePasswordCheck.html", controller: "view_filePasswordCheck" });
              modalInstance_askSelectedOjectedPassword.result.then(
                function passwordHasBeenEntered() {
                  local_deleteSelectedObject(isObjectInSafeFolder);
                },
                function passwordHasNotBeenEntered() {
                  notificationFactory.warning("You must provide a password in order to delete \"" + $scope.selected.current.name + "\". Please try again.");
                }
              );
            }
            else
              local_deleteSelectedObject(isObjectInSafeFolder);
          },
          function safeCheckFailure() {
            notificationFactory.warning("You must provide the \"Safe\" password in order to delete any \"Safe\"-based file or folder. Please try again.");
          }
        );
      }
    }
  };



  /* ==================== CREATE OBJECT (UPLOAD FILE) ==================== */

  // Load order (for file)   : view_onNewFile() =>  local_setFileSecurity() =>  local_uploadFile();

  // Routine definition
  // Reset selected file to uplaod (view and scope)
  var resetUploadFileField = function() {
    $scope.view_newFile = "";
  };

  // "Upload file" button handler [3/3]
  var local_uploadFile = function(isNewFileInSafeFolder) {
    var local_fileData          = "";
    var local_fileStreamID      = "";
    var local_fileDataChunkSent = 0;

    notificationFactory.loading();
    $http.post($rootScope.api.url + "/cloud/stream/" + $scope.project.id + (isNewFileInSafeFolder ? "/" + $scope.safe.password : ""), { data: { filename: $scope.newFile.input.filename, path: $scope.filePath.current.split(",").join("/"), password: ($scope.newFile.isSecured ? $scope.newFile.password : "")}}, { headers: { "Authorization": $rootScope.user.token }}).then(
      function streamOpeningSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.3.1") {
          storeSafePassword();
          notificationFactory.info("Sending \"" + $scope.newFile.input.filename + "\"...");
          local_fileStreamID = response.data.data.stream_id;
          local_fileData = $scope.newFile.input.base64.match(/.{1,1048576}/g);
          for (var i = 0; i < local_fileData.length; ++i) {
            $http.put($rootScope.api.url + "/cloud/file", { data: { stream_id: local_fileStreamID, project_id: $scope.project.id, chunk_numbers: local_fileData.length, current_chunk: i, file_chunk: local_fileData[i] }}, { headers: { "Authorization": $rootScope.user.token }}).then(
              function chunkSendingSuccess(response) {
                ++local_fileDataChunkSent;
                notificationFactory.info("\"" + $scope.newFile.input.filename + "\" sent at " + parseInt(local_fileDataChunkSent / local_fileData.length * 100) + "%");
                if (local_fileDataChunkSent === local_fileData.length) {
                  $http.delete($rootScope.api.url + "/cloud/stream/" + $scope.project.id + "/" + local_fileStreamID, { headers: { "Authorization": $rootScope.user.token }}).then(
                    function streamClosingSuccess(response) {
                      notificationFactory.success("Sent: \"" + $scope.newFile.input.filename + "\"");
                      resetUploadFileField();
                      getCurrentFolderContent();
                    },
                    function streamClosingFailure(response) {
                      notificationFactory.warning("Unable to close stream for \"" + $scope.newFile.input.filename + "\". Please try again.");
                      resetUploadFileField();
                    }
                  );
                }
              },
              function chunkSendingFailure(response) {
                notificationFactory.warning("Chunk #" + i + " has failed");
                resetUploadFileField();
              }
            );
          }
        }
        else {
          notificationFactory.warning("Unable to upload \"" + $scope.newFile.input.filename + "\". Please try again.");
          resetSafePassword();
          resetUploadFileField();
        }
      },
      function streamOpeningFailure(response) {
        notificationFactory.warning("Unable to open stream for \"" + $scope.newFile.input.filename + "\". Please try again.");
      }
    );
  };

  // "Upload file" button handler [2/3]
  var local_setFileSecurity = function(isNewFileInSafeFolder) {
    var modalInstance_askIfNewFileIsProtected   = "";
    var modalInstance_askNewFileFirstPassword   = "";
    var modalInstance_askNewFileSecondPassword  = "";

    $scope.newFile.isSecured              = false;
    $scope.newFile.password               = "";
    $scope.newFile.password_confirmation  = "";

    modalInstance_askIfNewFileIsProtected = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_isNewFileProtected.html", controller: "view_isNewFileProtected" });
    modalInstance_askIfNewFileIsProtected.result.then(
      function fileWillHavePassword() {
        modalInstance_askNewFileFirstPassword = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_setNewFileFirstPassword.html", controller: "view_setNewFileFirstPassword" });
        modalInstance_askNewFileFirstPassword.result.then(
          function fileFirstPasswordEntered() {
            modalInstance_askNewFileSecondPassword = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_setNewFileSecondPassword.html", controller: "view_setNewFileSecondPassword" });
            modalInstance_askNewFileSecondPassword.result.then(
              function fileSecondPasswordEntered() {
                if ($scope.newFile.password === $scope.newFile.password_confirmation) {
                  $scope.newFile.isSecured = true;
                  local_uploadFile(isNewFileInSafeFolder);
                }
                else {
                  notificationFactory.warning("Passwords don't match. Please try again.");
                  local_setFileSecurity();
                }
              },
              function fileSecondPasswordNotEntered() {
                notificationFactory.warning("You must provide your password two times in order to confirm it. Please try again.");
                resetUploadFileField();
              }
            )
          },
          function fileFirstPasswordNotEntered() {
            notificationFactory.warning("Upload cancelled.");
            resetUploadFileField();
          }
        )
      },
      function fileWontHavePassword() {
        $scope.newFile.isSecured = false;
        local_uploadFile(isNewFileInSafeFolder);
      }
    )
  };

  // "Upload file" button handler [1/3]
  $scope.view_onNewFile = function() {
    var isNewFileInSafeFolder = "";
    var promise               = "";

    if ($scope.newFile.input) {
      isNewFileInSafeFolder = isPathInSafeFolder($scope.filePath.current);
      promise = (isNewFileInSafeFolder ? getSafePassword() : $q.when(true) );
      promise.then(
        function safeCheckSuccess() {
          local_setFileSecurity(isNewFileInSafeFolder);
        },
        function safeCheckFailure() {
          notificationFactory.warning("You must provide the \"Safe\" password in order to create any \"Safe\"-based file or folder. Please try again.");
        }
      );
    }
  };






  /* ==================== CREATE OBJECT (CREATE FOLDER) ==================== */

  // "Add folder" button handler
  $scope.view_onNewFolder = function() {
    var modalInstance_askForFolderName = "";
    var isNewFolderInSafeFolder        = "";
    var promise                        = "";

    modalInstance_askForFolderName = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "view_setNewFolder.html", controller: "view_setNewFolder" });
    modalInstance_askForFolderName.result.then(
      function folderNameProvided() {
        isNewFolderInSafeFolder = isPathInSafeFolder($scope.filePath.current);
        promise = (isNewFolderInSafeFolder ? getSafePassword() : $q.when(true) );
        promise.then(
          function safeCheckSuccess() {
            notificationFactory.loading("Creating folder...");
            $http.post($rootScope.api.url + "/cloud/createdir", { data: { project_id: $scope.project.id, path: $scope.filePath.current.split(",").join("/"), dir_name: $scope.newFolder.name, password: (isNewFolderInSafeFolder ? $scope.safe.password : "") }}, { headers: { "Authorization": $rootScope.user.token }}).then(
              function folderCreationSuccess(response) {
                if (response.data.info && response.data.info.return_code == "1.3.1") {
                  storeSafePassword();
                  $http.get($rootScope.api.url + "/cloud/list/" + $scope.project.id + "/," + $scope.filePath.current + (isNewFolderInSafeFolder ? "/" + $scope.safe.password : ""), { headers: { "Authorization": $rootScope.user.token }}).then(
                    function folderContentReceived(response) {
                      setDataOnSuccess(response);
                      notificationFactory.success("Folder \"" + $scope.newFolder.name + "\" created.");
                      $scope.newFolder.name = "";
                    },
                    function folderContentNotReceived(response) {
                      setDataOnFailure();
                    }
                  )
                }
                else {
                  notificationFactory.warning("Unable to create \"" + $scope.newFolder.name + "\" folder. Please try again.");
                  resetSafePassword();
                }
              },
              function folderCreationFailure(response) {
                notificationFactory.warning("Unable to create \"" + $scope.newFolder.name + "\" folder. Please try again.")
              }
            )
          },
          function safeCheckFailure() {
            notificationFactory.warning("You must provide the \"Safe\" password in order to create any \"Safe\"-based file or folder. Please try again.");
          }
        );
      },
      function folderNameNotProvided() {
      }
    );
  }

}]);



// Controller definition (from view)
// FOLDER CREATION => set folder name.
app.controller("view_setNewFolder", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_setNewFolderSuccess = function() {
    $scope.newFolder.error = ($scope.newFolder.name ? false : true);
    if (!$scope.newFolder.error)
      $uibModalInstance.close();
  };
  
  $scope.view_setNewFolderFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



// Controller definition (from view)
// FILE UPLOAD => is new file will be protected?
app.controller("view_isNewFileProtected", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_newFileIsProtected = function() {
    $uibModalInstance.close();
  };
  
  $scope.view_newFileIsNotProtected = function() {
    $uibModalInstance.dismiss();
  };
}]);



// Controller definition
// FILE UPLOAD => set first password for new file.
app.controller("view_setNewFileFirstPassword", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_newFileFirstPasswordSuccess = function() {
    $scope.newFile.error = ($scope.newFile.password ? false : true);
    if (!$scope.newFile.error)
      $uibModalInstance.close();
  };

  $scope.view_newFileFirstPasswordFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



// Controller definition
// FILE UPLOAD => set second password for new file (verification).
app.controller("view_setNewFileSecondPassword", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_newFileSecondPasswordSuccess = function() {
    $scope.newFile.error = ($scope.newFile.password_confirmation ? false : true);
    if (!$scope.newFile.error)
      $uibModalInstance.close();
  };

  $scope.view_newFileSecondPasswordFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



// Controller definition
// FILE DOWNLOAD => get password for file.
app.controller("view_filePasswordCheck", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_filePasswordCheckSuccess = function() {
    $scope.selected.error = ($scope.selected.password ? false : true);
    if (!$scope.selected.error)
      $uibModalInstance.close();
  };
  
  $scope.view_filePasswordCheckFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);



// Controller definition
// SAFE ACCESS => get password for the "Safe" folder.
app.controller("view_safePasswordCheck", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.view_safePasswordCheckSuccess = function() {
    $scope.safe.error = ($scope.safe.password ? false : true);
    if (!$scope.safe.error)
      $uibModalInstance.close();
  };
  
  $scope.view_safePasswordCheckFailure = function() {
    $uibModalInstance.dismiss();
  };
}]);