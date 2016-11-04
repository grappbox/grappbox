/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP project settings
app.controller("ProjectSettingsController", ["$http", "$location", "notificationFactory", "$rootScope", "$route", "$routeParams", "$scope", "$uibModal",
    function($http, $location, notificationFactory, $rootScope, $route, $routeParams, $scope, $uibModal) {


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

    $http.put($rootScope.api.url + "/projects/"+ $scope.projectID, data, { headers: {"Authorization": $rootScope.user.token }})
      .then(function successCallback(response) {
        notificationFactory.success("Project updated");
        $location.path("/settings/" + $scope.projectID);
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.2.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      }, $scope);
      $scope.editMode = false;
  };

  $scope.createProject = function(project){
    //var logo = ;
    if (project.password != project.confirm_password) {
      notificationFactory.warning("'Cloud Password' and 'Confirmation' should be the same !");
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

    $http.post($rootScope.api.url + "/project", data, { headers: { "Authorization": $rootScope.user.token }})
      .then(function successCallback(response) {
        $scope.data.project_error = false;
        $scope.data.project_new = false;
        $scope.projectID = (response.data && response.data.data && Object.keys(response.data.data).length ? response.data.data.id : null);
        notificationFactory.success("Project created.");
        $location.path("./");
      },
      function errorCallback(response) {
        notificationFactory.warning("Unable to create project. Please try again.");
      }, $scope);

  };

  $scope.retrieveProject = function(){
    $http.get($rootScope.api.url + "/project/retrieve/" + $scope.projectID, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        notificationFactory.success("Project retrieved.");
        $route.reload();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.5.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.warning("Unable to retrieve project. Please try again.");
            break;
          }
        }
        else {
          notificationFactory.warning("Unable to retrieve project. Please try again.");
        }

      }, $scope);
  };

  $scope.updatePassword = function(project){
    if (!project.password ||! project.confirm_password || !project.old_password) {
      return 0;
    }

    if (project.password != project.confirm_password) {
      notificationFactory.warning("'New password' and 'Confirmation' should be the same !");
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

    $http.put($rootScope.api.url + "/project/"+ $scope.projectID, data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        notificationFactory.success("Password updated");
        $location.path("/settings/" + $scope.projectID);
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.2.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      }, $scope);
  }


  // "Delete project" button handler
  $scope.action.onDeleteProject = function() {
    var modal_deleteProject = $uibModal.open({ animation: true, size: "lg", backdrop: "static", templateUrl: "modal_deleteProject.html", controller: "modal_deleteProject" });
    modal_deleteProject.result.then(
      function onModalConfirm(data) {
        notificationFactory.success("Project successfully deleted.");
        $http.delete($rootScope.api.url + "/project/" + $scope.projectID,{headers: {"Authorization": $rootScope.user.token}}).then(
          function onDeleteProjectSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.6.1")
              notificationFactory.error();
            else
              notificationFactory.success("Project successfully deleted.");
            $route.reload();
          },
          function onDeleteProjectFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "6.4.3":
                $rootScope.reject();
                break;

                case "6.4.9":
                notificationFactory.warning("You don't have sufficient rights to perform this operation.");
                break;

                default:
                notificationFactory.error();
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
              $rootScope.reject();
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
            $rootScope.reject();
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
    var elem = {
      //"token": $rootScope.user.token,
      "projectId": $scope.projectID,
      "name": new_customer.name
    };
    var data = {"data": elem};

    $http.post($rootScope.api.url + "/project/customeraccess", data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        notificationFactory.success("Customer access created");
        getCustomers();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.6.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      }, $scope);
  };

  $scope.deleteCustomerAccess = function(customer) {
    $http.delete($rootScope.api.url + "/project/customeraccess/" + $scope.projectID + "/" + customer.id, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        notificationFactory.success("Customer access deleted.");
        getCustomers();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.9.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
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
              $rootScope.reject();
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
            $rootScope.reject();
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
    var elem = {//"token": $rootScope.user.token,
                "id": $scope.projectID,
                "email": new_user};
    var data = {"data": elem};
    $http.post($rootScope.api.url + "/project/user", data, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        getUsers();
        notificationFactory.success("User added.");
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.10.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      }, $scope);
  };

  $scope.removeUser = function(user) {
    $http.delete($rootScope.api.url + "/project/user/" + $scope.projectID + "/" + user.id, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        getUsers();
        notificationFactory.success("User removed.");
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "6.11.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      }, $scope);
  };

  $scope.changeProjectOwner = function() {
    // TODO ???
  };

  $scope.assignRoleToUser = function(user) {
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
              notificationFactory.success("User role changed.");
            },
            function errorCallback(response) {
              getUsersRoles();
              notificationFactory.warning("Unable to change user role. Please try again.");
            }, $scope);
        },
        function errorCallback(response) {
          getUsersRoles();
          if (response.data.info.return_code) {
            switch(response.data.info.return_code) {

              case "13.5.9":
              notificationFactory.warning("You don't have enought rights for this action.");
              break;

              default:
              notificationFactory.error();
              break;
            }
          }
          else {
            notificationFactory.error();
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
          notificationFactory.success("User role changed");
        },
        function errorCallback(response) {
          getUsersRoles();
          if (response.data.info.return_code) {
            switch(response.data.info.return_code) {

              case "13.5.9":
              notificationFactory.warning("You don't have enought rights for this action.");
              break;

              default:
              notificationFactory.error();
              break;
            }
          }
          else {
            notificationFactory.error();
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
            $rootScope.reject();
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
        notificationFactory.success("Role edited.");

        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.3.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      // TODO check error -> do switch
      });
  };

  $scope.createRole = function(new_role) {
    console.log($scope.new_role);
    console.log(new_role);

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
        notificationFactory.success("Role created.");
        getRoles();
        // TODO recharge role list for users
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.1.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
        }
      // TODO check error -> do switch
      });
  };

  $scope.deleteRole = function(role) {
    $http.delete($rootScope.api.url + "/role/" + role.roleId, {headers: {"Authorization": $rootScope.user.token}})
      .then(function successCallback(response) {
        notificationFactory.success("Role deleted.");
        // TODO remove users assign to role
        // TODO recharge role list for users
        getRoles();
      },
      function errorCallback(response) {
        if (response.data.info.return_code) {
          switch(response.data.info.return_code) {

            case "13.2.9":
            notificationFactory.warning("You don't have enought rights for this action.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else {
          notificationFactory.error();
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
