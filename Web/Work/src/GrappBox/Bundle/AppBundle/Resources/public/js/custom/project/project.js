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

  //Get project informations if not new
  if ($scope.projectID != 0) {
    $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project_new = false;
        $scope.data.project = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
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

  // ------------------------------------------------------
  //                EDITION SWITCH
  // ------------------------------------------------------
  $scope.editMode = false;

  $scope.project_switchEditMode = function() {
    $scope.editMode = ($scope.editMode ? false : true);
  };

  // ------------------------------------------------------
  //                PROJECT
  // ------------------------------------------------------
  $scope.updateProject = function(project){
    //var logo = ;
    var elem = {
      "token": $cookies.get('USERTOKEN'),
      "projectId": $scope.projectID,
      "name": project.name,
      "description": project.description,
      //"logo": logo,
      //"password": project.new_password,
      "phone": project.phone,
      "company": project.company,
      "email": project.contact_mail,
      "facebook": project.facebook,
      "twitter": project.twitter
    };
    var data = {"data": elem};

    Notification.info({ message: 'Updating project...', delay: 5000 });
    $http.put($rootScope.apiBaseURL + '/projects/updateinformations', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Project updated', delay: 5000 });
        $location.path('/project/' + $scope.projectID);
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to update project. Please try again.', delay: 5000 });
      }, $scope);
      $scope.editMode = false;
  };

  $scope.createProject = function(project){
    //var logo = ;
    var elem = {
      "token": $cookies.get('USERTOKEN'),
      "name": project.name,
      "description": project.description,
      //"logo": logo,
      //"password": project.new_password
      "phone": project.phone,
      "company": project.company,
      "email": project.contact_mail,
      "facebook": project.facebook,
      "twitter": project.twitter
    };
    var data = {"data": elem};

    Notification.info({ message: 'Creating project...', delay: 5000 });
    $http.post($rootScope.apiBaseURL + '/projects/projectcreation', data)
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project_new = false;
        $scope.projectID = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data.id : null);
        // TODO create roles
        // TODO assign users to project and to their roles
        // TODO generate customer access
        Notification.success({ message: 'Project created', delay: 5000 });
        $location.path('/project/' + $scope.projectID);
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to create project. Please try again.', delay: 5000 });
      }, $scope);

  };

  $scope.deleteProject = function(){
    Notification.info({ message: 'Deleting project...', delay: 5000 });
    $http.delete($rootScope.apiBaseURL + '/projects/delproject/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        Notification.success({ message: 'Project deleted', delay: 5000 });
        $location.path('/project/' + $scope.projectID);
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to delete project. Please try again.', delay: 5000 });
      }, $scope);
  };

  $scope.retrieveProject = function(){
    Notification.info({ message: 'Retrieving project...', delay: 5000 });
    $http.get($rootScope.apiBaseURL + '/projects/retrieveproject/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        Notification.success({ message: 'Project retrieved', delay: 5000 });
        $route.reload();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to retrieve project. Please try again.', delay: 5000 });
      }, $scope);
  };

  // ------------------------------------------------------
  //                PASSWORD
  // ------------------------------------------------------

  // TODO

  // ------------------------------------------------------
  //                CUSTOMER ACCESS
  // ------------------------------------------------------

  // TODO

  // ------------------------------------------------------
  //                ROLES AND USERS
  // ------------------------------------------------------

  // TODO


}]);
