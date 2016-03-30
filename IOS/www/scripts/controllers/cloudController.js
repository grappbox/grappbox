/*
    Summary: TIMELINES Controller
*/

angular.module('GrappBox.controllers')
.controller('CloudCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, $ionicPopup, $ionicModal, $cordovaFile, $cordovaFileTransfer, $timeout,
    CloudLS, CloudCreateDir, CloudDownloadFile, CloudOpenStream, CloudCloseStream, CloudUploadChunk) {

    $scope.projectId = $stateParams.projectId;
    $scope.userConnectedId = $rootScope.userDatas.id;

    //Refresher
    $scope.doRefresh = function () {
        $scope.CloudLS(true);
        console.log("View refreshed !");
    }

    $scope.pathTab = [];
    $scope.password = "";
    $scope.path = "";
    $scope.file = {};
    
    // Add directory name to pathTab and add it to path
    $scope.pushDirToPath = function (dirName) {
        $scope.pathTab.push(dirName + "/");
        console.log("Forward to: " + $scope.pathTab[$scope.pathTab.length - 1]);
        $scope.path = $scope.path + $scope.pathTab[$scope.pathTab.length - 1];
    }

    // Remove directory name from pathTab, reinitialize path to "," and add all pathTab rows to path
    $scope.GoBack = function () {
        $scope.pathTab.pop();
        console.log("Backward to: " + $scope.pathTab[$scope.pathTab.length - 1]);
        $scope.path = ",";
        for (var i = 0; i < $scope.pathTab.length; i++)
            $scope.path = $scope.path + $scope.pathTab[i];
        $scope.CloudLS(true);
    }

    /*
    ** Cloud LS
    ** Method: GET
    */
    $scope.cloudData = {};
    $scope.CloudLS = function (isBack, dirName, isSecured) {
        if (dirName)
            $scope.pushDirToPath(dirName);
        else if (isBack)
            $scope.path = $scope.path;
        else
            $scope.path = "/";
        console.log("path at the end = " + $scope.path);
        $rootScope.showLoading();
        CloudLS.get({
            token: $rootScope.userDatas.token,
            idProject: $scope.projectId,
            path: $scope.path.replace(/\//g, ",")
        }).$promise
            .then(function (data) {
                console.log('Cloud LS successful !');
                console.log(data);
                $scope.cloudData = data.data.array;
            })
            .catch(function (error) {
                console.error('Cloud LS failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.CloudLS();

    /*
    ** Create dir
    ** Method: POST
    */
    $scope.dir = {};
    $scope.createDirData = {};
    $scope.CloudCreateDir = function () {
        $rootScope.showLoading();
        console.log("token = '" + $rootScope.userDatas.token + "' , project_id = '" + $scope.projectId + "' , path = '" + $scope.path + "' , dir_name = '" + $scope.dir.dirName + "'");
        CloudCreateDir.save({
            data: {
                token: $rootScope.userDatas.token,
                project_id: $scope.projectId,
                path: $scope.path,
                dir_name: $scope.dir.dirName
            }
        }).$promise
            .then(function (data) {
                console.log('Cloud create dir successful !');
                console.log(data);
                $scope.createDirData = data.data;
                $scope.CloudLS(true);
                $scope.dirModal.hide();
            })
            .catch(function (error) {
                console.error('Cloud create dir failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }

    //Search for createDirModal.html ng-template in cloud.html
    $ionicModal.fromTemplateUrl('createDirModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function (modal) {
        $scope.dirModal = modal;
    });

    //Destroy the modal
    $scope.$on('$destroy', function () {
        $scope.dirModal.remove();
    });

    $scope.downloadFile = function (fileName) {
        document.addEventListener('deviceready', function () {
            // File for download
            var url = $rootScope.API + 'cloud/file/' + $scope.path.replace(/\//g, ",") + fileName + '/' + $rootScope.userDatas.token + '/' + $scope.projectId;
            // Save location
            var targetPath = cordova.file.externalDataDirectory + fileName;
            console.log("targetPath = " + "'" + targetPath + "'");
            // Trust every host certificate SSL
            var trustHosts = true;
            // Options to send
            var options = {};
            $cordovaFileTransfer.download(url, targetPath, options, trustHosts)
              .then(function (result) {
                  console.log(result);
              }, function (error) {
                  console.error(error);
              }, function (progress) {
                  $timeout(function () {
                      $scope.downloadProgress = (progress.loaded / progress.total) * 100;
                  })
              });
        }, false);
    }

    /*
    ** Get html redirection to download a file
    ** Method: GET
    */
    /*$scope.downloadData = {};
    $scope.CloudDownloadFile = function (fileName) {
        $rootScope.showLoading();
        console.log("CloudPath = '" + $scope.path + fileName + "', token = '" + $rootScope.userDatas.token + "' , idProject = '" + $scope.projectId + "'");
        CloudDownloadFile.get({
            CloudPath: $scope.path.replace(/\//g, ",") + fileName,
            token: $rootScope.userDatas.token,
            idProject: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('Cloud download file successful !');
                console.log(data);
                $scope.downloadData = data.data;
                //$scope.downloadFile(data, fileName);
            })
            .catch(function (error) {
                console.error('Cloud download file failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }*/

    /*
    ** Open stream in order to upload file
    ** Method: POST
    */
    $scope.openStreamData = {};
    $scope.CloudOpenStream = function () {
        $rootScope.showLoading();
        CloudOpenStream.save({
            token: $rootScope.userDatas.token,
            project_id: $scope.projectId,
            data: {
                path: $scope.path,
                filename: $scope.fileName
            }
        }).$promise
            .then(function (data) {
                console.log('Cloud open stream successful !');
                console.log(data);
                $scope.openStreamData = data.data;
                $scope.CloudLS(true);
            })
            .catch(function (error) {
                console.error('Cloud open stream failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    
    // Chunk from string is the faster solution
    function chunkString(str, len) {
        var _size = Math.ceil(str.length / len),
            _ret = new Array(_size),
            _offset
        ;

        for (var _i = 0; _i < _size; _i++) {
            _offset = _i * len;
            _ret[_i] = str.substring(_offset, _offset + len);
        }

        return _ret;
    }
})