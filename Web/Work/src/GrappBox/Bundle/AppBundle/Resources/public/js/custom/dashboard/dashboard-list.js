/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP dashboard list
*
*/
app.controller("dashboardListController", ["$rootScope", "$scope", "$http", "$cookies", "localStorageService", "$base64", "$location", function($rootScope, $scope, $http, $cookies, localStorageService, $base64, $location) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.content = { onLoad: true, isValid: false };
  $scope.data = { projects: [] };
  $scope.method = { loadProject: "" };

  // Get user current projects (and progress)
  $http.get($rootScope.apiBaseURL + "/dashboard/getprojectsglobalprogress/" + $cookies.get("USERTOKEN"))
    .then(function onGetSuccess(response) {
      $scope.projects = [];

      if (response.data.info && response.data.info.return_code == "1.2.1")
        $scope.data.projects = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      else
        $scope.data.projects = null;
      $scope.content.isValid = true;
      $scope.content.onLoad = false;
    },
    function onGetFail(response) {
      if (response.data.info && response.data.info.return_code == "2.3.3")
        $rootScope.onUserTokenError();
      $scope.data.projects = null;
      $scope.content.isValid = false;
      $scope.content.onLoad = false;
    });


    /* ==================== PROJECT LOADING ==================== */

    // Set active project ID local storage
    $scope.method.loadProject = function(projectID) {
      localStorageService.set("ACTIVE_PROJECT", $base64.encode(projectID));
      $location.path("./dashboard/" + projectID);
    };

}]);