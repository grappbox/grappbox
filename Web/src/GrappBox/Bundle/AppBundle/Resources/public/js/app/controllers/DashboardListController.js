/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP dashboard list
app.controller("DashboardListController", ["$base64", "$http", "localStorageService", "$location", "$rootScope", "$scope",
    function($base64, $http, localStorageService, $location, $rootScope, $scope) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { load: true, valid: false };
  $scope.method = { loadProject: "" };
  $scope.projects = {};

  $rootScope.path.current = "/";



  /* ==================== ACTIVE PROJECT MANAGEMENT ==================== */

  // Routine definition
  // Check and initialize local storage
  $scope.method.loadProject = function(project) {
    localStorageService.set("project.set", true);
    localStorageService.set("project.id", $base64.encode(project.id));
    localStorageService.set("project.name", $base64.encode(project.name));

    $rootScope.project.id = project.id;
    $rootScope.project.name = project.name;
    $rootScope.project.set = true;

    $location.path("/dashboard/" + project.id);
  };



  /* ==================== GLOBAL PROGRESS ==================== */

  // Get user current projects (and progress)
  $http.get($rootScope.api.url + "/dashboard/projects", { headers: { 'Authorization': $rootScope.user.token }}).then(
    function onGetGlobalProgressSuccess(response) {
      if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code == "1.2.1")
        $scope.projects = (response && response.data && response.data.data && response.data.data.array ? response.data.data.array : null);
      else
        $scope.projects = null;
      $scope.view.valid = true;
      $scope.view.load = false;
    },
    function onGetGlobalProgressFail(response) {
      if (response && response.data && response.data.info && response.data.info.return_code && response.data.info.return_code == "2.3.3")
        $rootScope.reject();
      $scope.data.projects = null;
      $scope.view.valid = false;
      $scope.view.load = false;
    }
  );

}]);