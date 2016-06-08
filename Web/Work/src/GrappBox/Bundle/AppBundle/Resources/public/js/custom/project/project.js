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
app.controller('projectController', ['$rootScope', '$scope', '$routeParams', '$http', '$cookies', 'Notification', '$route', '$location', function($rootScope, $scope, $routeParams, $http, $cookies, Notification, $route, $location) {

  // ------------------------------------------------------
  //                DATA FORMATING
  // ------------------------------------------------------

  // Date format
  $scope.formatObjectDate = function(dateToFormat) {
    return (dateToFormat ? dateToFormat.substring(0, dateToFormat.lastIndexOf(":")) : "N/A");
  };

  // ------------------------------------------------------
  //                EDITION SWITCH
  // ------------------------------------------------------

  $scope.project_switchEditMode = function() {
    $scope.data.editMode = ($scope.data.editMode ? false : true);
  };

  // ------------------------------------------------------
  //                PROJECT
  // ------------------------------------------------------
  $scope.updateProject = function(project){
    //var logo = ;

    var encrypted_password = project.password;
    var elem = {
      "token": $cookies.get('USERTOKEN'),
      "projectId": $scope.projectID,
      "name": project.name,
      "description": project.description,
      //"logo": logo,
      //"password": encrypted_password,
      "oldPassword": project.old_password,
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

    var encrypted_password = project.password;
    var elem = {
      "token": $cookies.get('USERTOKEN'),
      "name": project.name,
      "description": project.description,
      //"logo": logo,
      "password": encrypted_password,
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
        //$location.path('/project/' + $scope.projectID);
        $route.reload();
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

  var getCustomers = function() {
    $scope.data.customersLoad = true;

    $http.get($rootScope.apiBaseURL + '/projects/getcustomeraccessbyproject/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.customers_error = false;
        $scope.data.customers = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.customersLoad = false;
      },
      function errorCallback(response) {
        $scope.data.customers_error = true;
        $scope.data.customers = null;
        $scope.data.customersLoad = false;
      });
  };

  $scope.createCustomersAccess = function(new_customer) {
    Notification.info({ message: 'Creating customer access...', delay: 5000 });
    var elem = {
      "token": $cookies.get('USERTOKEN'),
      "projectId": $scope.projectID,
      "name": new_customer.name
    };
    var data = {"data": elem};

    $http.post($rootScope.apiBaseURL + '/projects/generatecustomeraccess', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Customer access created', delay: 5000 });
        getCustomers();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to create customer access. Please try again.', delay: 5000 });
      }, $scope);
  };

  $scope.deleteCustomerAccess = function(customer) {
    Notification.info({ message: 'Deleting customer access...', delay: 5000 });
    $http.delete($rootScope.apiBaseURL + '/projects/delcustomeraccess/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + customer.id)
      .then(function successCallback(response) {
        Notification.success({ message: 'Customer access deleted', delay: 5000 });
        getCustomers();
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to delete customer access. Please try again.', delay: 5000 });
      }, $scope);
  };


  // ------------------------------------------------------
  //                USERS
  // ------------------------------------------------------

  var getUsersRoles = function() {
    for (var i = 0; i < ($scope.data.roles).length; i++) {
      $http.get($rootScope.apiBaseURL + '/roles/getusersforrole/' + $cookies.get('USERTOKEN') + '/' + $scope.data.roles[i].id)
        .then(function successCallback(response) {
          $scope.data.users_error = false;
          $scope.data.usersroles = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : {});
          for (var x = 0; x < ($scope.data.users).length; x++) {
            for (var y = 0; y < ($scope.data.usersroles.users_assigned).length; y++) {
              if ($scope.data.usersroles.users_assigned[y].id == $scope.data.users[x].id)
              {
                $scope.data.users[x].actualRole = $scope.data.usersroles.id;
                $scope.data.users[x].role = $scope.data.usersroles.id;
              }
            }
          }
        },
        function errorCallback(response) {
          $scope.data.users_error = true;
        });
    }
  };

  var getUsers = function() {
    $scope.data.usersLoad = true;

    $http.get($rootScope.apiBaseURL + '/projects/getusertoproject/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.users_error = false;
        $scope.data.users = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        getUsersRoles();
        $scope.data.usersLoad = false;
      },
      function errorCallback(response) {
        $scope.data.users_error = true;
        $scope.data.users = null;
        $scope.data.usersLoad = false;
      });
  };

  $scope.addUser = function(new_user) {
    Notification.info({ message: 'Adding user...', delay: 5000 });
    var elem = {"token": $cookies.get('USERTOKEN'),
                "id": $scope.projectID,
                "email": new_user};
    var data = {"data": elem};
    $http.post($rootScope.apiBaseURL + '/projects/addusertoproject', data)
      .then(function successCallback(response) {
        getUsers();
        Notification.success({ message: 'User added', delay: 5000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to add user. Please try again.', delay: 5000 });
      }, $scope);
  };

  $scope.removeUser = function(user) {
    Notification.info({ message: 'Removing user...', delay: 5000 });
    $http.delete($rootScope.apiBaseURL + '/projects/removeusertoproject/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + user.id)
      .then(function successCallback(response) {
        getUsers();
        Notification.success({ message: 'User removed', delay: 5000 });
      },
      function errorCallback(response) {
        Notification.warning({ message: 'Unable to remove user. Please try again.', delay: 5000 });
      }, $scope);
  };

  $scope.changeProjectOwner = function() {

  };

  $scope.assignRoleToUser = function(user) {
    Notification.info({ message: 'Changing user role...', delay: 5000 });
    if (user.actualRole) {
      $http.delete($rootScope.apiBaseURL + '/roles/delpersonrole/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID + '/' + user.id + '/' + user.actualRole)
        .then(function successCallback(response) {
          var elem = {"token": $cookies.get('USERTOKEN'),
                      "userId": user.id,
                      "roleId": user.role};
          var data = {"data": elem};
          $http.post($rootScope.apiBaseURL + '/roles/assignpersontorole', data)
            .then(function successCallback(response) {
              getUsersRoles();
              Notification.success({ message: 'User role changed', delay: 5000 });
            },
            function errorCallback(response) {
              getUsersRoles();
              Notification.warning({ message: 'Unable to change user role. Please try again.', delay: 5000 });
            }, $scope);
        },
        function errorCallback(response) {
          getUsersRoles();
          Notification.warning({ message: 'Unable to change user role. Please try again.', delay: 5000 });
        }, $scope);
    }
    else {
      var elem = {"token": $cookies.get('USERTOKEN'),
                  "userId": user.id,
                  "roleId": user.role};
      var data = {"data": elem};
      $http.post($rootScope.apiBaseURL + '/roles/assignpersontorole', data)
        .then(function successCallback(response) {
          getUsersRoles();
          Notification.success({ message: 'User role changed', delay: 5000 });
        },
        function errorCallback(response) {
          getUsersRoles();
          Notification.warning({ message: 'Unable to change user role. Please try again.', delay: 5000 });
        }, $scope);
    }
  };


  // ------------------------------------------------------
  //                ROLES
  // ------------------------------------------------------

  var rightsOptionsList ={
        0: "No access",
        1: "Read only",
        2: "Read & write"
    };
  $scope.rightsOptions = Object.keys(rightsOptionsList).map(function (key) {
        return { id: key, name: rightsOptionsList[key] };
    });
  $scope.convertToInt = function(nb){
      return parseInt(nb, 10);
  };

  var getRoles = function(getUserFct) {
    $http.get($rootScope.apiBaseURL + '/roles/getprojectroles/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.roles_error = false;
        $scope.data.roles = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        if (getUserFct)
          getUsers();
        $scope.data.rolesLoad = false;
      },
      function errorCallback(response) {
        $scope.data.roles_error = true;
        $scope.data.roles = null;
        $scope.data.rolesLoad = false;
      });
  };

  $scope.editRole = function(role) {
    Notification.info({ message: 'Editing role...', delay: 5000 });
    var elem = {
      "token": $cookies.get('USERTOKEN'),
		  "roleId": role.id,
		  "name": role.name,
		  "teamTimeline": role.team_timeline,
	    "customerTimeline": role.customer_timeline,
	    "gantt": role.gantt,
      "whiteboard": role.whiteboard,
      "bugtracker": role.bugtracker,
      "event": role.event,
      "task": role.task,
      "projectSettings": role.project_settings,
      "cloud": role.cloud
    };
    var data = {"data": elem };

    $http.put($rootScope.apiBaseURL + '/roles/putprojectroles', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Role edited', delay: 5000 });

        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
          Notification.warning({ message: 'Unable to edit role. Please try again.', delay: 5000 });
      // TODO check error -> do switch
      });
  };

  $scope.createRole = function(new_role) {
    Notification.info({ message: 'Creating role...', delay: 5000 });

    var elem = {"token": $cookies.get('USERTOKEN'),
	              "projectId": $scope.projectID,
	              "name": new_role.name,
	              "teamTimeline": new_role.team_timeline,
	              "customerTimeline": new_role.customer_timeline,
	              "gantt": new_role.gantt,
                "whiteboard": new_role.whiteboard,
                "bugtracker": new_role.bugtracker,
                "event": new_role.event,
                "task": new_role.task,
                "projectSettings": new_role.project_settings,
                "cloud": new_role.cloud  };
    var data = {"data": elem };

    $http.post($rootScope.apiBaseURL + '/roles/addprojectroles', data)
      .then(function successCallback(response) {
        Notification.success({ message: 'Role created', delay: 5000 });
        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
          Notification.warning({ message: 'Unable to create role. Please try again.', delay: 5000 });
      // TODO check error -> do switch
      });
  };

  $scope.deleteRole = function(role) {
    Notification.info({ message: 'Deleting role...', delay: 5000 });
    $http.delete($rootScope.apiBaseURL + '/roles/delprojectroles/' + $cookies.get('USERTOKEN') + '/' + role.id)
      .then(function successCallback(response) {
        Notification.success({ message: 'Role deleted', delay: 5000 });
        // TODO remove users assign to role
        // TODO recharge role list for users
        getRoles();
      },
      function errorCallback(response) {
          Notification.warning({ message: 'Unable to delete role. Please try again.', delay: 5000 });
        // TODO check error -> do switch
      });
  };



  // ------------------------------------------------------
  //                TAB MANAGEMENT
  // ------------------------------------------------------

    $scope.lastTabContent = "#general-content";
    $scope.lastTabTitle = "#general-title";
    
    $scope.displayTab = function(tabContent, tabTitle) {

      $($scope.lastTabContent)[0].classList.remove("active");
      $($scope.lastTabTitle)[0].classList.remove("active");

      $(tabContent)[0].classList.add("active");
      $(tabTitle)[0].classList.add("active");
      $scope.lastTabContent = tabContent;
      $scope.lastTabTitle = tabTitle;
    };

  // ------------------------------------------------------
  //                PAGE INITILIZATION
  // ------------------------------------------------------

  var content= "";

  // Scope variables initialization
  $scope.data = { onLoad: true, customersLoad: true, usersLoad: true, project: { }, customers: { }, isValid: false, canEdit: true, editMode: false };
  $scope.projectID = $routeParams.id;


  //Get project informations if not new
  if ($scope.projectID != 0) {
    $scope.data.project_new = false;

    // $http.get($rootScope.apiBaseURL + '/roles/getuserroleforpart/' + $cookies.get('USERTOKEN') + '/' + $scope.user.id + '/' + $scope.projectID + '/project_settings')
    //   .then(function successCallback(response) {
    //     $scope.data.canEdit = (response.data && response.data.data && Object.keys(response.data.data).length && response.data.data.value > 1 ? true : false);
    //   },
    //   function errorCallback(response) {
    //     $scope.data.canEdit = false;
    //   });

    $http.get($rootScope.apiBaseURL + '/projects/getinformations/' + $cookies.get('USERTOKEN') + '/' + $scope.projectID)
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
        $scope.data.onLoad = false;
      },
      function errorCallback(response) {
        $scope.data.project_error = true;
        $scope.data.project = null;
        $scope.data.onLoad = false;
      });

    getRoles(true);
    getCustomers();
  }
  else {
    $scope.data.project_new = true;
    $scope.data.onLoad = false;
    $scope.data.project_error = false;
  }

}]);
