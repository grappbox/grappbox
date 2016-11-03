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
app.controller("DashboardListController", ["$rootScope", "$scope", "localStorageService", "$base64", "$location", "$http",
    function($rootScope, $scope, localStorageService, $base64, $location, $http) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false };
  $scope.method = { loadProject: "" };
  $scope.projects = {};



  /* ==================== ACTIVE PROJECT MANAGEMENT ==================== */

  // Routine definition
  // Check and initialize local storage
  $scope.method.loadProject = function(project) {
    localStorageService.set("HAS_PROJECT", true);
    localStorageService.set("PROJECT_ID", $base64.encode(project.id));
    localStorageService.set("PROJECT_NAME", $base64.encode(project.name));

    $scope.project.id = project.id;
    $scope.project.name = project.name;
    $scope.project.set = true;

    $location.path("/dashboard/" + project.id);
  };



  /* ==================== GLOBAL PROGRESS ==================== */

  // Get user current projects (and progress)
  $http.get($rootScope.api.url + "/dashboard/projects", { headers: { 'Authorization': $rootScope.user.token }}).then(
    function onGetGlobalProgressSuccess(response) {
      if (response.data.info && response.data.info.return_code == "1.2.1")
        $scope.projects = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
      else
        $scope.projects = null;
      $scope.view.valid = true;
      $scope.view.onLoad = false;
    },
    function onGetGlobalProgressFail(response) {
      if (response.data.info && response.data.info.return_code == "2.3.3")
        $rootScope.userTokenError();
      $scope.data.projects = null;
      $scope.view.valid = false;
      $scope.view.onLoad = false;
    }
  );

}]);
