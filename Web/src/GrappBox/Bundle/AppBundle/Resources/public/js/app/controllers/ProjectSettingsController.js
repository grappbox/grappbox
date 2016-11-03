/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/



/* ===================================================== */
/* ==================== PAGE ACCESS ==================== */
/* ===================================================== */

/**
* Routine definition
* APP project settings page access
*
*/

// Check if requested project is accessible
var isProjectSettingsPageAccessible = function($q, $http, $rootScope, $route, $location, Notification) {
  var deferred = $q.defer();

  // Project creation page
  if ($route.current.params.project_id == 0) {
    deferred.resolve();
    return deferred.promise;
  }

  $http.get($rootScope.api.url + "/project/" + $route.current.params.project_id, {headers: {"Authorization": $rootScope.user.token}})
    .then(function onGetSuccess(response) {
      deferred.resolve();
    },
    function onGetFail(response) {
      if (response.data.info.return_code) {
        switch(response.data.info.return_code) {
          case "6.3.3":
          deferred.reject();
          $rootScope.onUserTokenError();
          break;

          case "6.3.4":
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "Project not found.", delay: 10000 });
          break;

          case "6.3.9":
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "You don\'t have access to the settings of this project.", delay: 10000 });
          break;

          default:
          deferred.reject();
          $location.path("./");
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
          break;
        }
      }
      else {
        deferred.reject();
        $location.path("./");
        Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
      }
    });

    return deferred.promise;
};

// "isProjectSettingsPageAccessible" routine injection
isProjectSettingsPageAccessible["$inject"] = ["$q", "$http", "$rootScope", "$route", "$location", "Notification"];



/* ====================================================== */
/* ==================== PAGE CONTENT ==================== */
/* ====================================================== */

/**
* Controller definition
* APP project page
*
*/
app.controller("ProjectSettingsController", ["$rootScope", "$scope", "$routeParams", "$http", "$uibModal", "Notification", "$route", "$location",
    function($rootScope, $scope, $routeParams, $http, $uibModal, Notification, $route, $location) {


  // ------------------------------------------------------
  //                PAGE INITILIZATION
  // ------------------------------------------------------

  var content= "";

  // Scope variables initialization
  $scope.data = { onLoad: true, project: { }, customers: { }, message: "_invalid", users_message: "_invalid", roles_message: "_invalid", customers_message:"_invalid", userRights: false, editMode: false };
  $scope.projectID = $routeParams.project_id;
  $scope.action = { deleteProject: "" };
  $scope.new_role = { };

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
      //"token": $rootScope.user.token,
      //"projectId": $scope.projectID,
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

    Notification.info({ message: "Updating project...", delay: 5000 });
    $http.put($rootScope.api.url + "/projects/"+ $scope.projectID, data, { headers: {"Authorization": $rootScope.user.token }})
      .then(function successCallback(response) {
        Notification.success({ message: "Project updated", delay: 5000 });
        $location.path("/settings/" + $scope.projectID);
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.2.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      }, $scope);
      $scope.editMode = false;
  };

  $scope.createProject = function(project){
    //var logo = ;
    if (project.password != project.confirm_password) {
      Notification.warning({ message: "'Cloud Password' and 'Confirmation' should be the same !", delay: 5000 });
      return 0;
    }

    var encrypted_password = project.password;
    var elem = {
      //"token": $rootScope.user.token,
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

    Notification.info({ message: "Creating project...", delay: 5000 });
    $http.post($rootScope.api.url + "/project", data, { headers: { 'Authorization': $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project_new = false;
        $scope.projectID = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data.id : null);
        Notification.success({ message: "Project created", delay: 5000 });
        $location.path("./");
      },
      function errorCallback(response) {
        Notification.warning({ message: "Unable to create project. Please try again.", delay: 5000 });
      }, $scope);

  };

  $scope.retrieveProject = function(){
    Notification.info({ message: "Retrieving project...", delay: 5000 });
    $http.get($rootScope.api.url + "/project/retrieve/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Project retrieved", delay: 5000 });
        $route.reload();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.5.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "Unable to retrieve project. Please try again.", delay: 5000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "Unable to retrieve project. Please try again.", delay: 5000 });
        }

      }, $scope);
  };

  $scope.updatePassword = function(project){
    if (!project.password ||! project.confirm_password || !project.old_password) {
      return 0;
    }

    if (project.password != project.confirm_password) {
      Notification.warning({ message: "'New password' and 'Confirmation' should be the same !", delay: 5000 });
      return 0;
    }

    var encrypted_password = project.password;
    var elem = {
      //"token": $rootScope.user.token,
      //"projectId": $scope.projectID,
      "password": encrypted_password,
      "oldPassword": project.old_password
    };
    var data = {"data": elem};

    Notification.info({ message: "Updating password...", delay: 5000 });
    $http.put($rootScope.api.url + "/project/"+ $scope.projectID, data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Password updated", delay: 5000 });
        $location.path("/settings/" + $scope.projectID);
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.2.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
        }
      }, $scope);
  }


  // "Delete project" button handler
  $scope.action.onDeleteProject = function() {
    var modal_deleteProject = $uibModal.open({ animation: true, size: "lg", backdrop: "static", templateUrl: "modal_deleteProject.html", controller: "modal_deleteProject" });
    modal_deleteProject.result.then(
      function onModalConfirm(data) {
        Notification.success({ title: "Project", message: "Project successfully deleted.", delay: 2000 });
        $http.delete($rootScope.api.url + "/project/" + $scope.projectID,{headers: {"Authorization": $rootScope.user.token}}).then(
          function onDeleteProjectSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.6.1")
              Notification.error({ title: "Project", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
            else
              Notification.success({ title: "Project", message: "Project successfully deleted.", delay: 2000 });
            $route.reload();
          },
          function onDeleteProjectFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "6.4.3":
                $rootScope.onUserTokenError();
                break;

                case "6.4.9":
                Notification.error({ title: "Project", message: "You don't have sufficient rights to perform this operation.", delay: 3000 });
                break;

                default:
                Notification.error({ title: "Project", message: "An error occurred. Please try again.", delay: 3000 });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };

  var getProjectInfo = function() {
    //Get project informations if not new
    if ($scope.projectID != 0) {
      $scope.data.project_new = false;

      $http.get($rootScope.api.url + "/project/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
        .then(function successCallback(response) {
          $scope.data.project = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : null);
          $scope.data.message = (response.data.info && response.data.info.return_code == "1.6.1" ? "_valid" : "_empty");
          $scope.data.onLoad = false;
        },
        function errorCallback(response) {
          $scope.data.project = null;
          $scope.data.onLoad = false;

          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "6.3.3":
              $rootScope.onUserTokenError();
              break;

              case "6.3.9":
              $scope.data.message = "_denied";
              break;

              default:
              $scope.data.message = "_invalid";
              break;
            }
        });

        getRoles(true);
        getCustomers();
        getRights();
    }
    else {
      $scope.data.project_new = true;
      $scope.data.onLoad = false;
      $scope.data.message = "_valid";
    }
  };

  var getRights = function() {

    $http.get($rootScope.api.url + "/role/user/part/" + $scope.user.id + "/" + $scope.projectID + "/project_settings", {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.userRights = (response.data && response.data.data && Object.keys(response.data.data).length && response.data.data.value ? response.data.data.value : false);
      },
      function errorCallback(response) {
        $scope.data.userRights = false;
      });
  }


  // ------------------------------------------------------
  //                CUSTOMER ACCESS
  // ------------------------------------------------------

  var getCustomers = function() {

    $scope.data.customersLoad = true;

    $http.get($rootScope.api.url + "/project/customeraccesses/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.customers = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.customers_message = (response.data.info && response.data.info.return_code == "1.6.1" ? "_valid" : "_empty");
        $scope.data.customersLoad = false;
      },
      function errorCallback(response) {
        $scope.data.customers = null;
        $scope.data.customersLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "6.8.3":
            $rootScope.onUserTokenError();
            break;

            case "6.8.9":
            $scope.data.customers_message = "_denied";
            break;

            default:
            $scope.data.customers_message = "_invalid";
            break;
          }
      });
  };

  $scope.createCustomersAccess = function(new_customer) {
    Notification.info({ message: "Creating customer access...", delay: 5000 });
    var elem = {
      //"token": $rootScope.user.token,
      "projectId": $scope.projectID,
      "name": new_customer.name
    };
    var data = {"data": elem};

    $http.post($rootScope.api.url + "/project/customeraccess", data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Customer access created", delay: 5000 });
        getCustomers();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.6.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      }, $scope);
  };

  $scope.deleteCustomerAccess = function(customer) {
    Notification.info({ message: "Deleting customer access...", delay: 5000 });
    $http.delete($rootScope.api.url + "/project/customeraccess/" + $scope.projectID + "/" + customer.id, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Customer access deleted", delay: 5000 });
        getCustomers();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.9.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      }, $scope);
  };


  // ------------------------------------------------------
  //                USERS
  // ------------------------------------------------------

  var getUsersRoles = function() {
    for (var i = 0; i < ($scope.data.roles).length; i++) {
      $http.get($rootScope.api.url + "/role/users/" + $scope.data.roles[i].roleId, {headers: {"Authorization": $rootScope.user.token}})
        .then(function successCallback(response) {
          $scope.data.usersroles = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data : {});
          $scope.data.users_message = (response.data.info && response.data.info.return_code == "1.13.1" ? "_valid" : "_empty");
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
          if (response.data.info && response.data.info.return_code)
            switch(response.data.info.return_code) {
              case "13.10.3":
              $rootScope.onUserTokenError();
              break;

              case "13.10.9":
              $scope.data.message = "_denied";
              break;

              default:
              $scope.data.message = "_invalid";
              break;
            }
        });
    }
  };

  var getUsers = function() {

    $scope.data.usersLoad = true;

    $http.get($rootScope.api.url + "/project/users/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.users = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.users_message = (response.data.info && response.data.info.return_code == "1.6.1" ? "_valid" : "_empty");
        getUsersRoles();
        $scope.data.usersLoad = false;
      },
      function errorCallback(response) {
        $scope.data.users = null;
        $scope.data.usersLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "6.12.3":
            $rootScope.onUserTokenError();
            break;

            case "6.12.9":
            $scope.data.message = "_denied";
            break;

            default:
            $scope.data.message = "_invalid";
            break;
          }
      });
  };

  $scope.addUser = function(new_user) {
    Notification.info({ message: "Adding user...", delay: 5000 });
    var elem = {//"token": $rootScope.user.token,
                "id": $scope.projectID,
                "email": new_user};
    var data = {"data": elem};
    $http.post($rootScope.api.url + "/project/user", data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        getUsers();
        Notification.success({ message: "User added", delay: 5000 });
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.10.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      }, $scope);
  };

  $scope.removeUser = function(user) {
    Notification.info({ message: "Removing user...", delay: 5000 });
    $http.delete($rootScope.api.url + "/project/user/" + $scope.projectID + "/" + user.id, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        getUsers();
        Notification.success({ message: "User removed", delay: 5000 });
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.11.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      }, $scope);
  };

  $scope.changeProjectOwner = function() {
    // TODO ???
  };

  $scope.assignRoleToUser = function(user) {
    Notification.info({ message: "Changing user role...", delay: 5000 });
    if (user.actualRole) {
      var elem = {//"token": $rootScope.user.token,
                  "userId": user.id,
                  "roleId": user.role};
      var data = {"data": elem};
      $http.post($rootScope.api.url + "/role/user", data, {headers: {"Authorization": $rootScope.user.token}})
        .then(function successCallback(response) {
          $http.delete($rootScope.api.url + "/role/user/" + $scope.projectID + "/" + user.id + "/" + user.actualRole, {headers: {"Authorization": $rootScope.user.token}})
            .then(function successCallback(response) {
              getUsersRoles();
              Notification.success({ message: "User role changed", delay: 5000 });
            },
            function errorCallback(response) {
              getUsersRoles();
              Notification.warning({ message: "Unable to change user role. Please try again.", delay: 5000 });
            }, $scope);
        },
        function errorCallback(response) {
          getUsersRoles();
          if (response.data.info.return_code) {
            switch(response.data.info.return_code) {

              case "13.5.9":
              Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
              break;

              default:
              Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
              break;
            }
          }
          else {
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
          }
        }, $scope);
    }
    else {
      var elem = {//"token": $rootScope.user.token,
                  "userId": user.id,
                  "roleId": user.role};
      var data = {"data": elem};
      $http.post($rootScope.api.url + "/role/user", data, {headers: {"Authorization": $rootScope.user.token}})
        .then(function successCallback(response) {
          getUsersRoles();
          Notification.success({ message: "User role changed", delay: 5000 });
        },
        function errorCallback(response) {
          getUsersRoles();
          if (response.data.info.return_code) {
            switch(response.data.info.return_code) {

              case "13.5.9":
              Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
              break;

              default:
              Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
              break;
            }
          }
          else {
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
          }
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

    $scope.data.rolesLoad = true;

    $http.get($rootScope.api.url + "/roles/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        $scope.data.roles = (response.data && response.data.data && Object.keys(response.data.data.array).length ? response.data.data.array : null);
        $scope.data.roles_message = (response.data.info && response.data.info.return_code == "1.13.1" ? "_valid" : "_empty");
        if (getUserFct)
          getUsers();
        $scope.data.rolesLoad = false;
      },
      function errorCallback(response) {
        $scope.data.roles = null;
        $scope.data.rolesLoad = false;

        if (response.data.info && response.data.info.return_code)
          switch(response.data.info.return_code) {
            case "13.4.3":
            $rootScope.onUserTokenError();
            break;

            case "13.4.9":
            $scope.data.roles_message = "_denied";
            break;

            default:
            $scope.data.roles_message = "_invalid";
            break;
          }
      });
  };

  $scope.editRole = function(role) {
    Notification.info({ message: "Editing role...", delay: 5000 });
    var elem = {
      //"token": $rootScope.user.token,
		  //"roleId": role.roleId,
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

    $http.put($rootScope.api.url + "/role/" + role.roleId, data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Role edited", delay: 5000 });

        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.3.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      // TODO check error -> do switch
      });
  };

  $scope.createRole = function(new_role) {
    console.log($scope.new_role);
    console.log(new_role);
    Notification.info({ message: "Creating role...", delay: 5000 });

    var elem = {//"token": $rootScope.user.token,
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

    $http.post($rootScope.api.url + "/role", data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Role created", delay: 5000 });
        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.1.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
      // TODO check error -> do switch
      });
  };

  $scope.deleteRole = function(role) {
    Notification.info({ message: "Deleting role...", delay: 5000 });
    $http.delete($rootScope.api.url + "/role/" + role.roleId, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        Notification.success({ message: "Role deleted", delay: 5000 });
        // TODO remove users assign to role
        // TODO recharge role list for users
        getRoles();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.2.9":
            Notification.warning({ message: "You don\'t have enought rights for this action.", delay: 10000 });
            break;

            default:
            Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
            break;
          }
        }
        else {
          Notification.warning({ message: "An error occurred. Please try again.", delay: 10000 });
        }
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

    getProjectInfo();
}]);


/**
* Controller definition (from view)
* PROJECT DELETION => confirmation prompt.
*
*/
app.controller("modal_deleteProject", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_confirmProjectDeletion = function() { $uibModalInstance.close(); };
  $scope.modal_cancelProjectDeletion = function() { $uibModalInstance.dismiss(); };
}]);
