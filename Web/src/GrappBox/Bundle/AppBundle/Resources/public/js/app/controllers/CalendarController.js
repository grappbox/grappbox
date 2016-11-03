/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Module configuation
* APP calendar page
*
*/
app.config(["calendarConfig", function(calendarConfig) {
  calendarConfig.dateFormatter = "moment";
  calendarConfig.allDateFormats.moment.date.hour = "HH:mm";
  calendarConfig.allDateFormats.moment.title.day = "ddd D MMM";
  calendarConfig.displayAllMonthEvents = true;
  calendarConfig.displayEventEndTimes = true;
  calendarConfig.showTimesOnWeekView = true;
}]);



/**
* Controller definition
* APP calendar page
*
*/
app.controller("CalendarController", ["$rootScope", "$scope", "$q", "$http", "moment", "Notification", "$uibModal",
    function($rootScope, $scope, $q, $http, moment, Notification, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Third-party library variables initialization
  var vm = this;
  
  vm.date = new Date();
  vm.title = "";
  vm.mode = "month";
  vm.cellOpen = true;
  vm.events = [];

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.modal = { onLoad: true, valid: false, authorized: false };

  $scope.data = { events: [], tasks: [], projects: [], types: [], members: [] };
  $scope.action = { onRefreshView: "", onNewEvent: "", onEditEvent: "", onDeleteEvent: "", onProjectChange: "" };
  $scope.new = { title: "", description: "", date: { begin: "", end: "" }, type: [], project: [], members: [] };
  $scope.edit = { title: "", description: "", date: { begin: "", end: "" }, type: [], project: [], members: [] };
  $scope.current = { project_id: "", members: [] };



  /* ==================== ROUTINES (LOCAL) ==================== */

  // Routine definition
  // Get project team members
  var _getTeamMembers = function(project_id) {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/projects/getusertoproject/" + $rootScope.user.token + "/" + project_id).then(
      function onGetTeamMembersSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.6.1":
            for (var i = 0; i < response.data.data.array.length; ++i)
              $scope.data.members.push({ project_id: project_id, members: { id: response.data.data.array[i].id, name: response.data.data.array[i].firstname + " " + response.data.data.array[i].lastname } });
            deferred.resolve();
            break;

            case "1.6.3":
            deferred.resolve();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            deferred.reject();
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
          deferred.reject();
        }
      },
      function onGetTeamMembersFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "6.12.3":
            deferred.reject();
            $rootScope.onUserTokenError();
            break;

            case "6.12.9":
            Notification.error({ message: "You don't have sufficient rights to perform this operation.<i class=\"material-icons\">clear</i>", delay: 3500 });
            $scope.view.authorized = false;
            deferred.reject();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            deferred.reject();
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
          deferred.reject();
        }
      }
    );
    return deferred.promise;
  };

  // Routine definition
  // Get user projects
  var _getUserProjects = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/user/getprojects/" + $rootScope.user.token).then(
      function onGetProjectsSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.7.1":
            $scope.data.projects = response.data.data.array;
            for (var i = 0; i < $scope.data.projects.length; ++i)
              _getTeamMembers($scope.data.projects[i].id);
            deferred.resolve();
            break;

            case "1.7.3":
            $scope.data.projects = [];
            deferred.resolve();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            deferred.reject();
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
          deferred.reject();
        }
      },
      function onGetProjectsFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "7.7.3":
            $rootScope.onUserTokenError();
            deferred.reject();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            deferred.reject();
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
          deferred.reject();
        }
      }
    );
    return deferred.promise;
  };

  // Routine definition
  // Get event types
  var _getEventTypes = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/event/gettypes/" + $rootScope.user.token).then(
      function onGetTypesSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.5.1":
            $scope.data.types = response.data.data.array;
            deferred.resolve();
            break;

            case "1.5.3":
            $scope.data.types = [];
            deferred.resolve();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            deferred.reject();
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
          deferred.reject();
        }
      },
      function onGetTypesFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "5.1.3":
            deferred.reject();
            $rootScope.onUserTokenError();
            break;

            default:
            deferred.reject();            
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            break;
          }
        }
        else {
          deferred.reject();          
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
        }
      }
    );
    return deferred.promise;
  };

  // Routine definition
  // Find project position in array by ID
  var _getProjectIndex = function(id) {
    for (var i = 0; i < $scope.data.projects.length; ++i)
      if ($scope.data.projects[i].id == id)
        return i;
    return null;
  };

  // Routine definition
  // Get user month planning (based on given start date)
  var _getUserPlanning = function(mode, date) {
    $http.get($rootScope.api.url + "/planning/get" + mode + "/" + $rootScope.user.token + "/" + date).then(
      function onGetPlanningSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.5.1":
            var events = response.data.data.array.events;
            var tasks = response.data.data.array.tasks;
            vm.events = [];

            if (events && events.length)
              for (var i = 0; i < events.length; ++i)
                vm.events.push({
                  id: events[i].id,
                  projectId: (events[i].projectId ? events[i].projectId : null),
                  projectName: (events[i].projectId ? $scope.data.projects[_getProjectIndex(events[i].projectId)].name : null),
                  typeName: events[i].type.name,
                  typeId: events[i].type.id,
                  title: events[i].title,
                  description: events[i].description,
                  startsAt: moment(events[i].beginDate.date).toDate(),
                  endsAt: moment(events[i].endDate.date).toDate(),
                  draggable: false,
                  resizable: false
                });
            break;

            case "1.5.3":
            $scope.data.events = [];
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
        }
      },
      function onGetPlanningFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "5.3.3":
            $rootScope.onUserTokenError();
            break;

            default:
            Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            break;
          }
        }
        else {
          Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
        }
      }
    );
  };



  /* ==================== EXECUTION ==================== */

  $scope.view.authorized = true;
  $scope.view.valid = true;

  var userProjects_promise = _getUserProjects();
  userProjects_promise.then(
    function onGetUserProjectSuccess() {
      var eventTypes_promise = _getEventTypes();
      eventTypes_promise.then(
        function onGetEventTypesSuccess() {
          $scope.view.onLoad = false;

          /* ==================== REFRESH OBJECT (EVENT) ==================== */

          // "Previous/Today/Next" button handler
          $scope.action.onRefreshView = function() {
            _getUserPlanning((vm.mode == "year" ? "month" : vm.mode), moment($scope.view.date).startOf("month").format("YYYY-MM-DD"));
          };

          // Calendar mode (day/week/month/year) change watch
          $scope.$watch("vm.mode", function() {
            $scope.action.onRefreshView();
          });
        },
        function onGetEventTypesFail() {
          $scope.view.valid = false;
        }
      ),
      function onGetUserProjectFail() {
        $scope.view.valid = false;
      }
    }
  );



  /* ==================== CREATE OBJECT (EVENT) ==================== */

  // Routine definition
  // Compile project data based on project id
  var _setCurrentProjectData = function(project_id) {
    $scope.current.project_id = "";
    $scope.current.members = [];
    for (var i = 0; i < $scope.data.members.length; ++i)
      if ($scope.data.members[i].project_id == project_id) {
        $scope.current.members.push($scope.data.members[i].members);
        $scope.current.project_id = project_id;
      }
  };

  // Routine definition
  // Set team members and project id based on selected project
  $scope.action.onProjectChange = function(project_id) {
    _setCurrentProjectData(project_id);
  };

  // "Create event" button handler
  $scope.action.onNewEvent = function() {
    $scope.current.project_id = "";
    $scope.current.members = [];
    
    var modal_newEvent = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_createNewEvent.html", controller: "modal_createNewEvent" });
    modal_newEvent.result.then(
      function onModalConfirm() {
        $http.post($rootScope.api.url + "/event/postevent", {
        data: {
          token: $rootScope.user.token,
          projectId: ($scope.new.project ? $scope.new.project.id : null),
          title: $scope.new.title,
          description: $scope.new.description,
          begin: $scope.new.date.begin,
          end: $scope.new.date.end,
          typeId: $scope.new.type.id,
          icon: "DATA" // TEMP
        }}).then(
          function onPostEventSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.5.1")
              Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            else
              Notification.success({ message: "Event successfully created.", delay: 2000 });
            $scope.action.onRefreshView();      
          },
          function onPostEventFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "5.4.3":
                $rootScope.onUserTokenError();
                break;

                case "5.4.9":
                Notification.error({ message: "You don't have sufficient rights to perform this operation.<i class=\"material-icons\">clear</i>", delay: 3500 });
                break;

                default:
                Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };




  /* ==================== EDIT OBJECT (EVENT) ==================== */

  // "Edit event" button handler
  $scope.action.onEditEvent = function(event) {
    $scope.edit = { id: event.id, title: event.title, description: event.description, date: { begin: event.startsAt, end: event.endsAt }, type: { id: event.typeId, name: event.typeName }, project: { id: event.projectId, name: event.projectName } };

    var modal_editEvent = $uibModal.open({ animation: true, size: "lg", backdrop: "static", scope: $scope, templateUrl: "modal_editEvent.html", controller: "modal_editEvent" });
    modal_editEvent.result.then(
      function onModalConfirm() {
        $http.put($rootScope.api.url + "/event/editevent", {
        data: (!$scope.edit.project.id ?
          { token: $rootScope.user.token, eventId: $scope.edit.id, title: $scope.edit.title, description: $scope.edit.description, begin: $scope.edit.date.begin, end: $scope.edit.date.end, typeId: $scope.edit.type.id, icon: "DATA" } : 
          { token: $rootScope.user.token, eventId: $scope.edit.id, title: $scope.edit.title, description: $scope.edit.description, begin: $scope.edit.date.begin, end: $scope.edit.date.end, typeId: $scope.edit.type.id, icon: "DATA", projectId: $scope.edit.project.id })}).then(
          function onPutEventSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.5.1")
              Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            else
              Notification.success({ message: "Event successfully edited.", delay: 2000 });
            $scope.action.onRefreshView();      
          },
          function onPutEventFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "5.5.3":
                $rootScope.onUserTokenError();
                break;

                case "5.5.9":
                Notification.error({ message: "You don't have sufficient rights to perform this operation.<i class=\"material-icons\">clear</i>", delay: 3500 });
                break;

                default:
                Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      }
    );
  };



  /* ==================== DELETE OBJECT (EVENT) ==================== */

  // "Delete event" button handler
  $scope.action.onDeleteEvent = function(event) {
    var modal_deleteEvent = $uibModal.open({ animation: true, size: "lg", backdrop: "static", windowClass: "submodal", templateUrl: "modal_deleteEvent.html", controller: "modal_deleteEvent" });
    modal_deleteEvent.result.then(
      function onModalConfirm(data) {
        $http.delete($rootScope.api.url + "/event/delevent/" + $rootScope.user.token + "/" + event.id).then(
          function onDeleteEventSuccess(response) {
            if (response.data.info && response.data.info.return_code !== "1.5.1")
              Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
            else
              Notification.success({ message: "Event successfully deleted.", delay: 2000 });
            $scope.action.onRefreshView();
          },
          function onDeleteEventFail(response) {
            if (response.data.info)
              switch(response.data.info.return_code) {
                case "5.6.3":
                $rootScope.onUserTokenError();
                break;

                case "5.6.9":
                Notification.error({ message: "You don't have sufficient rights to perform this operation.", delay: "false" });
                break;

                default:
                Notification.error({ message: "Someting is wrong with GrappBox. Please try again.<i class=\"material-icons\">warning</i>", delay: "false" });
                break;
              }
            }
          ),
        function onModalDismiss() { }
      });
    };

}]);



/**
* Controller definition (from view)
* EVENT CREATION => new message form.
*
*/
app.controller("modal_createNewEvent", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, description: false, date_begin: false, date_end: false, type: false };

  $scope.modal_confirmEventCreation = function() {
    $scope.error.title = ($scope.new.title && $scope.new.title.length > 0 ? false : true);
    $scope.error.description = ($scope.new.description && $scope.new.description.length > 0 ? false : true);
    $scope.error.date_begin = ($scope.new.date.begin && $scope.new.date.begin !== "" ? false : true);
    $scope.error.date_end = ($scope.new.date.end && $scope.new.date.end !== "" ? false : true);
    $scope.error.type = ($scope.new.type && Object.keys($scope.new.type).length > 0 ? false : true);

    var hasErrors = false;
    angular.forEach($scope.error, function(value, key) {
      if (value)
        hasErrors = true;
    });
    if (!hasErrors)
      $uibModalInstance.close();
  };
  $scope.modal_cancelEventCreation = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* EVENT EDITION => edit message form.
*
*/
app.controller("modal_editEvent", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
  $scope.error = { title: false, description: false, date_begin: false, date_end: false, type: false };

  $scope.modal_confirmEventEdition = function() {
    $scope.error.title = ($scope.edit.title && $scope.edit.title.length > 0 ? false : true);
    $scope.error.description = ($scope.edit.description && $scope.edit.description.length > 0 ? false : true);
    $scope.error.date_begin = ($scope.edit.date.begin && $scope.edit.date.begin !== "" ? false : true);
    $scope.error.date_end = ($scope.edit.date.end && $scope.edit.date.end !== "" ? false : true);
    $scope.error.type = ($scope.edit.type && Object.keys($scope.edit.type).length > 0 ? false : true);

    var hasErrors = false;
    angular.forEach($scope.error, function(value, key) {
      if (value)
        hasErrors = true;
    });
    if (!hasErrors)
      $uibModalInstance.close();
  };
  $scope.modal_cancelEventEdition = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* EVENT DELETION => confirmation prompt.
*
*/
app.controller("modal_deleteEvent", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.modal_confirmEventDeletion = function() { $uibModalInstance.close(); };
  $scope.modal_cancelEventDeletion = function() { $uibModalInstance.dismiss(); };
}]);