/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP project page
*
*/
app.controller('projectController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', function($rootScope, $scope, $routeParams, $http, $cookies, Notification) {
  var content= "";

  // Scope variables initialization
  $scope.data = { onLoad: true, project: { }, isValid: false, canEdit: false };
  $scope.projectID = $routeParams.id;

  $scope.data.availableColors = [
    { name: "-None-", value: "none" }, { name: "Red", value: "#F44336" }, { name: "Pink", value: "#E91E63" }, { name: "Purple", value: "#9C27B0" },
    { name: "Deep Purple", value: "#673AB7" }, { name: "Indigo", value: "#3F51B5" }, { name: "Blue", value: "#2196F3" }, { name: "Light Blue", value: "#03A9F4" },
    { name: "Cyan", value: "#00BCD4" }, { name: "Teal", value: "#009688" }, { name: "Green", value: "#4CAF50" }, { name: "Light Green", value: "#8BC34A" },
    { name: "Lime", value: "#CDDC39" }, { name: "Yellow", value: "#FFEB3B" }, { name: "Amber", value: "#FFC107" }, { name: "Orange", value: "#FF9800" },
    { name: "Deep Orange", value: "#FF5722" }, { name: "Brown", value: "#795548" }, { name: "Blue Grey", value: "#607D8B" }, { name: "White", value: "#FFFFFF" },
    { name: "Grey 20%", value: "#EEEEEE" }, { name: "Grey 40%", value: "#BDBDBD" }, { name: "Grey 50%", value: "#9E9E9E" }, { name: "Grey 60%", value: "#757575" },
    { name: "Grey 80%", value: "#424242" }, { name: "Black", value: "#000000" }
  ];


  //Get bugtracker informations if not new
  if ($scope.projectID != 0) {
    $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project_new = false;
        $scope.data.project = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        getColors();
        $scope.data.onLoad = false;
        //TODO check if user can edit settings
      },
      function errorCallback(response) {
        $scope.data.project_error = true;
        $scope.data.project_new = false;
        $scope.data.project = null;
        $scope.data.onLoad = false;
      });
  }
  else {
    $scope.data.onLoad = false;
    $scope.data.project_error = false;
    $scope.data.project_new = true;
  }

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  $scope.updateProject = function(project){

  };

  $scope.createProject = function(project){

  };
}]);
