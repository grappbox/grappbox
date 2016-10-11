/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')

.controller('CloudCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicModal, $cordovaFile, $cordovaFileTransfer, $timeout, $http, Toast, Cloud) {

    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.viewColor = $rootScope.GBNavColors.cloud;
    });

    $scope.projectId = $stateParams.projectId;
    $scope.userConnectedId = $rootScope.userDatas.id;

    //Refresher
    $scope.doRefresh = function () {
        $scope.CloudLS(true);
        console.log("View refreshed !");
    }

    $scope.pathTab = [];
    $scope.safePassword = {};
    $scope.filePassword = {};
    $scope.fileDeletePassword = {};
    $scope.wantPassword = {};
    $scope.wantPassword.choice = false;
    $scope.path = "/";

    $scope.isPathInSafeFolder = function (path) {
        path = path.replace(/\//g, ",");
        return (path.indexOf(",Safe") == 0);
    };

    // Add directory name to pathTab and add it to path
    $scope.pushDirToPath = function (dirName) {
        $scope.pathTab.push(dirName + "/");
        console.log("Forward to: " + $scope.pathTab[$scope.pathTab.length - 1]);
        $scope.path = $scope.path + $scope.pathTab[$scope.pathTab.length - 1];
    };

    // Remove directory name from pathTab, reinitialize path to "," and add all pathTab rows to path
    $scope.GoBack = function () {
        $scope.pathTab.pop();
        console.log("Backward to: " + $scope.pathTab[$scope.pathTab.length - 1]);
        $scope.path = ",";
        for (var i = 0; i < $scope.pathTab.length; i++)
            $scope.path = $scope.path + $scope.pathTab[i];
        $scope.CloudLS(true);
    };

    /*
    ** Cloud LS
    ** Method: GET
    */
    $scope.cloudData = {};
    $scope.CloudLS = function (isBack, dirName) {
        if (dirName)
            $scope.pushDirToPath(dirName);
        else if (isBack)
            $scope.path = $scope.path;
        //$rootScope.showLoading();
        Cloud.List().get({
            idProject: $scope.projectId,
            path: $scope.path.replace(/\//g, ","),
            passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
        }).$promise
            .then(function (data) {
                console.log('Cloud LS successful !');
                console.log(data);
                if (data.info && data.info.return_code == "3.4.9") {
                    console.error(data.info.return_message);
                    $scope.GoBack();
                }
                else {
                    $scope.cloudData = data.data.array;
                    // Check if completely empty
                    if (data.data.array.length == 0) {
                        $scope.noDir = "There is no folder.";
                        $scope.noFile = "There is no file.";
                    }
                    else {
                        // Check if there is no files
                        for (var i = 0; i < data.data.array.length; i++) {
                            if (data.data.array[i].type == "file") {
                                $scope.noFile = false;
                                break;
                            }
                            $scope.noFile = "There is no file.";
                        }
                        // Check if there is no folder
                        for (var i = 0; i < data.data.array.length; i++) {
                            if (data.data.array[i].type == "dir") {
                                $scope.noDir = false;
                                break;
                            }
                            $scope.noDir = "There is no folder.";
                        }
                    }
                }
            })
            .catch(function (error) {
                console.error('Cloud LS failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                //$scope.GoBack();
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    };
    $scope.CloudLS();

    /*
    ** Create dir
    ** Method: POST
    */
    $scope.dir = {};
    $scope.createDirData = {};
    $scope.CloudCreateDir = function () {
        //$rootScope.showLoading();
        console.log("token = '" + $rootScope.userDatas.token + "' , project_id = '" + $scope.projectId + "' , path = '" + $scope.path + "' , dir_name = '" + $scope.dir.dirName + "'");
        Cloud.CreateDir().save({
            data: {
                project_id: $scope.projectId,
                path: $scope.path.replace(/,/g, "/"),
                dir_name: $scope.dir.dirName,
                passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
            }
        }).$promise
            .then(function (data) {
                console.log('Cloud create dir successful !');
                console.log(data);
                $scope.createDirData = data.data;
                Toast.show("Folder created");
                $scope.noFileOrDir = false;
                $scope.noDir = false;
                $scope.CloudLS(true);
                //$scope.dirModal.hide();
            })
            .catch(function (error) {
                console.error('Cloud create dir failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    };

    //Search for createDirModal.html ng-template in cloud.html
    /*$ionicModal.fromTemplateUrl('createDirModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function (modal) {
        $scope.dirModal = modal;
    });

    //Destroy the modal
    $scope.$on('$destroy', function () {
        $scope.dirModal.remove();
    });*/

    /*
    ** Download file with $cordovaFileTransfer
    ** Method: GET
    **
    ** File location on Android: Android\Data\com.grappbox.grappbox\Files\
    ** File location on iOS: Just work on Android for now, we have to check if iOS and then test to put file in "documentsDirectory" or "dataDirectory"
    ** http://ngcordova.com/docs/plugins/file/
    **
    ** http://ngcordova.com/docs/plugins/fileTransfer/
    */
    $scope.downloadFile = function (fileName) {
        //$rootScope.showLoading();
        if ($scope.filePassword.pass) {
            Cloud.DownloadSecure().get({
                cloudPath: $scope.path.replace(/\//g, ",") + fileName,
                idProject: $scope.projectId,
                password: $scope.filePassword.pass ? "/" + $scope.filePassword.pass : "",
                passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
            }).$promise
                .then(function (data) {
                    console.log('Get secured download redirection successful !');
                    $scope.downloadFileAfterRedirect(fileName, data.headers.location);
                })
                .catch(function (error) {
                    console.error('Get secured download redirection failed ! Reason: ' + error.status + ' ' + error.statusText);
                    console.error(error);
                })
                .finally(function () {
                    $scope.$broadcast('scroll.refreshComplete');
                    //$scope.hideLoading();
                })
        }
        else {
            Cloud.Download().get({
                cloudPath: $scope.path.replace(/\//g, ",") + fileName,
                idProject: $scope.projectId,
                passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
            }).$promise
                .then(function (data) {
                    console.log('Get download redirection successful !');
                    $scope.downloadFileAfterRedirect(fileName, data.headers.location);
                })
                .catch(function (error) {
                    console.error('Get download redirection failed ! Reason: ' + error.status + ' ' + error.statusText);
                    console.error(error);
                })
                .finally(function () {
                    $scope.$broadcast('scroll.refreshComplete');
                    //$scope.hideLoading();
                })
        }
    };

    $scope.downloadFileAfterRedirect = function (fileName, url) {
        document.addEventListener('deviceready', function () {
            // Save location
            var targetPath = cordova.file.externalDataDirectory + fileName;
            console.log("targetPath = " + "'" + targetPath + "'");
            // Trust every host certificate SSL
            var trustHosts = true;
            // Options to send
            var options = {};
            $cordovaFileTransfer.download(url, targetPath, options, trustHosts)
              .then(function (data) {
                  console.log('Cloud download file successful !');
                  console.log(data);
                  $scope.filePassword = {};
                  Toast.show("File downloaded");
              }, function (error) {
                  console.error('Cloud download file failed !');
                  console.error(error);
                  $scope.filePassword = {};
                  Toast.show("Download error");
              }, function (progress) {
                  $timeout(function () {
                      $scope.downloadProgress = (progress.loaded / progress.total) * 100;
                  })
              });
        }, false);
    };

    /*
    ** Open stream in order to upload file
    ** Method: POST
    */
    $scope.streamId = {};
    $scope.openStreamData = {};
    $scope.closeStreamData = {};
    $scope.fileChunks = {};

    $scope.uploadProgress = 0;

    $scope.UploadFile = function (fileUpload) {
        //$rootScope.showLoading();
        // We open stream
        Cloud.OpenStream().save({
            data: {
                "path": $scope.path.replace(/,/g, '/'),
                "filename": fileUpload.filename,
                "password": $scope.wantPassword.pass ? $scope.wantPassword.pass : ""
            },
            project_id: $scope.projectId,
            safe_password: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
        }).$promise
            .then(function (data) {
                console.log('Cloud open stream successful !');
                console.log(data);
                if (data.info && data.info.return_code == "3.1.9") {
                    console.error(data.info.return_message);
                    return;
                }
                $scope.openStreamData = data.data;
                $scope.streamId = data.data.stream_id;
                $scope.fileChunks = fileUpload.base64.match(/.{1,1048576}/g);
                Toast.show("Uploading...");
                console.log("$scope.fileChunks.length = " + $scope.fileChunks.length);
                // We loop to send all file chunks
                for (var i = 0; i < $scope.fileChunks.length; ++i) {
                    Cloud.UploadChunks().update({
                        data: {
                            project_id: $scope.projectId,
                            stream_id: $scope.streamId,
                            chunk_numbers: $scope.fileChunks.length,
                            current_chunk: i,
                            file_chunk: $scope.fileChunks[i]
                        }
                    }).$promise
                        .then(function (data) {
                            console.log('Cloud send chunk successful !');
                            $scope.uploadProgress = i / $scope.fileChunks.length * 100;
                            if (i === $scope.fileChunks.length) {
                                Cloud.CloseStream().delete({
                                    project_id: $scope.projectId,
                                    stream_id: $scope.streamId
                                }).$promise
                                .then(function (data) {
                                    // We close stream
                                    console.log('Cloud close stream successful !');
                                    Toast.show("File uploaded");
                                    $scope.noFile = false;
                                    $scope.noDir = false;
                                    $scope.closeStreamData = data.data;
                                    resetUploadFileField();
                                    $scope.CloudLS(true);
                                })
                                .catch(function (error) {
                                    console.error('Cloud close stream failed ! Reason: ' + error.status + ' ' + error.statusText);
                                    Toast.show("Upload error");
                                    console.log(error);
                                    resetUploadFileField();
                                    $scope.$broadcast('scroll.refreshComplete');
                                    $rootScope.hideLoading();
                                })
                                .finally(function () {
                                    $scope.$broadcast('scroll.refreshComplete');
                                    $rootScope.hideLoading();
                                })
                            }
                        })
                        .catch(function (error) {
                            console.error('Cloud send chunk failed ! Reason: ' + error.status + ' ' + error.statusText);
                            Toast.show("Upload error");
                            resetUploadFileField();
                            $scope.$broadcast('scroll.refreshComplete');
                            //$rootScope.hideLoading();
                        })
                }
            })
            .catch(function (error) {
                console.error('Cloud open stream failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Upload error");
                console.error(error);
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                resetUploadFileField();
            })
    };

    /*
    ** Delete file or folder
    ** Method: DELETE
    */
    $scope.deleteFileOrDirData = {};
    $scope.deleteFileOrFolder = function (fileName) {
        console.log("DEL ! path = " + $scope.path.replace(/\//g, ",") + fileName + " | safePassword = " + ($scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""));
        //$rootScope.showLoading();
        Cloud.DelFileOrDir().delete({
            project_id: $scope.projectId,
            path: $scope.path.replace(/\//g, ",") + fileName,
            passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
        }).$promise
            .then(function (data) {
                console.log('Cloud delete file or dir successful !');
                Toast.show("Deleted");
                console.log(data);
                $scope.deleteFileOrDirData = data;
                if (data.info && data.info.return_code == "3.7.9") {
                    console.error(data.info.return_message);
                }
                $scope.CloudLS(true);
            })
            .catch(function (error) {
                console.error('Cloud delete file or dir failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Delete error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                $scope.fileDeletePassword = {};
            })
    };

    /*
    ** Delete secured file
    ** Method: DELETE
    */
    $scope.deleteSecuredFileData = {};
    $scope.deleteSecuredFile = function (fileName) {
        console.log("DEL SECURED ! path = " + $scope.path.replace(/\//g, ",") + fileName + " | fileDeletePassword = " + $scope.fileDeletePassword.pass + " | safePassword = " + ($scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""));
        //$rootScope.showLoading();
        Cloud.DelSecuredFile().delete({
            project_id: $scope.projectId,
            path: $scope.path.replace(/\//g, ",") + fileName,
            password: $scope.fileDeletePassword.pass ? $scope.fileDeletePassword.pass : "",
            passwordSafe: $scope.isPathInSafeFolder($scope.path) ? $scope.safePassword.pass : ""
        }).$promise
            .then(function (data) {
                console.log('Cloud delete file secured successful !');
                Toast.show("Secure file deleted");
                console.log(data);
                $scope.deleteSecuredFileOrDirData = data;
                if (data.info && data.info.return_code == "3.9.9") {
                    console.error(data.info.return_message);
                }
                $scope.CloudLS(true);
            })
            .catch(function (error) {
                console.error('Cloud delete file or dir secured failed ! Reason: ' + error.status + ' ' + error.statusText);
                Toast.show("Delete error");
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
                $scope.fileDeletePassword = {};
            })
    };

    // Reset file input
    var resetUploadFileField = function () {
        angular.element(document.querySelector("#fileUploadId")).val("");
        $scope.fileUpload = {};
        $scope.wantPassword = {};
        $scope.uploadProgress = 0;
    };

    /*
    ********************* CHECKS *********************
    */

    // Check if directory is protected (safe). If it is: ask for safe password, if it is not, simple LS
    $scope.checkSafe = function (is_secured, dirName) {
        if (is_secured == true)
            $scope.showSafePopup(dirName);
        else
            $scope.CloudLS(false, dirName);
    };

    // Check for file protected or not. If it is: ask file password popup, if it is not, directly download file
    $scope.checkFileProtected = function (is_secured, fileName) {
        if (is_secured == true)
            $scope.showAskFilePasswordPopup(fileName);
        else
            $scope.downloadFile(fileName);
    };

    // Check for password && safe folder before deleting a file or a folder
    $scope.checkDeleteFile = function (fileName, is_secured) {
        // if file is secured and it is the safe folder then tell user he can't delete
        // else if it is a secured file in Safe folder then ask for password popup
        console.log(fileName);
        if (is_secured && fileName == "Safe" && ($scope.path == "," || $scope.path == "/")) {
            Toast.show("Can't delete 'Safe' folder");
            console.log("You can't delete the 'Safe' folder !");
        }
        else if (is_secured) {
            $scope.showFileDeletePopup(fileName);
        }
        else
            $scope.deleteFileOrFolder(fileName);
    };

    /*
    ********************* POPUPS *********************
    */

    // Enter file delete password popup
    $scope.showFileDeletePopup = function (fileName) {
        var myPopup = $ionicPopup.show({
            template: '<input type="password" placeholder="Your file password" ng-model="fileDeletePassword.pass">',
            title: 'Delete File',
            subTitle: 'Enter File Password',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.fileDeletePassword.pass) {
                          // Don't allow the user to close unless he enters file password
                          e.preventDefault();
                      } else {
                          return $scope.fileDeletePassword;
                      }
                  }
              }]
        })
        .then(function (res) {
            if (res && res.pass) {
                if ($scope.fileDeletePassword.pass != 'undefined')
                    $scope.deleteSecuredFile(fileName);
            }
            else {
                console.log('no pass');
                $scope.fileDeletePassword = {};
            }
        });
    };

    // Ask if user want to protect file before upload popup
    $scope.showAskPasswordChoicePopup = function (fileUpload) {
        var myPopup = $ionicPopup.show({
            template: '<ion-checkbox ng-model="wantPassword.choice">Password on file ?</ion-checkbox>'
                + '<input ng-show="wantPassword.choice == true" type="password" placeholder="Your password" ng-model="wantPassword.pass">',
            title: 'File Password',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      $scope.UploadFile(fileUpload);
                  }
              }]
        })
    };

    // Create directory popup
    $scope.showCreateDirPopup = function () {
        var myPopup = $ionicPopup.show({
            template: '<ion-checkbox ng-model="wantPassword.choice">Password on file ?</ion-checkbox>'
                + '<input ng-show="wantPassword.choice == true" type="password" placeholder="Your password" ng-model="wantPassword.pass">',
            template: '<ion-input class="item item-input">'
                + '<ion-label>Name</ion-label>'
                + '<input type="text" placeholder="My folder name" name="createDirectory_title" ng-model="dir.dirName" required/>'
                + '</ion-input>',
            title: 'Create directory',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Create</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.dir.dirName)
                          e.preventDefault();
                      else
                          $scope.CloudCreateDir();
                  }
              }]
        })
    };

    // Ask file password before download popup
    $scope.showAskFilePasswordPopup = function (fileName) {
        var myPopup = $ionicPopup.show({
            template: '<input type="password" ng-model="filePassword.pass">',
            title: 'Enter File Password',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.filePassword.pass) {
                          // Don't allow the user to close unless he enters file password
                          e.preventDefault();
                      } else {
                          return $scope.filePassword;
                      }
                  }
              }]
        })
        .then(function (res) {
            if (res && res.pass) {
                if ($scope.filePassword.pass != 'undefined')
                    $scope.downloadFile(fileName);
            }
            else {
                console.log('no pass');
                $scope.filePassword = {};
            }
        });
    };

    // Enter safe password popup
    $scope.showSafePopup = function (dirName) {
        var myPopup = $ionicPopup.show({
            template: '<input type="password" ng-model="safePassword.pass">',
            title: 'Enter Safe Password',
            subTitle: 'Password has been defined at your project creation',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.safePassword.pass) {
                          // Don't allow the user to close unless he enters safe password
                          e.preventDefault();
                      } else {
                          return $scope.safePassword;
                      }
                  }
              }]
        })
        .then(function (res) {
            if (res && res.pass) {
                if ($scope.safePassword.pass != 'undefined')
                    $scope.CloudLS(false, dirName);
            }
            else
                console.log('no pass');
        });
    };
})