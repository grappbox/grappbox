/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Module configuation
* APP calendar page content
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
* APP calendar page content
*
*/
app.controller("calendarController", ["$scope", "$q", "$http", "$rootScope", "moment", "Notification", "$uibModal", function($scope, $q, $http, $rootScope, moment, Notification, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.calendar = { events: [], types: [] };
  $scope.projects = [];

  $scope.content = { onLoad: true, isValid: true, isInit: false };
  $scope.view = { title: "", mode: "month", date: new Date(), isCellOpen: true };
  $scope.method = { onRefresh: "", onEventCreate: "", onEventEdit: "", onEventDelete: "" };



  /* ==================== START ==================== */

  // Routine definition
  // Get user projects, each with all team members
  var getUserProjectsWithMembers = function() {
    return $http.get($rootScope.api.url + "/user/getprojects/" + $rootScope.user.token)
    .then(function onGetSuccess(response) {
      $scope.projects = [];

      if (response.data.info && response.data.info.return_code == "1.7.1") {
        var data = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
        var projectList = [];

        for (var i = 0; i < data.length; ++i)
          projectList.push({ id: data[i].id, name: data[i].name });
        angular.forEach(projectList, function(projectData, projectKey) {
          $http.get($rootScope.api.url + "/projects/getusertoproject/" + $rootScope.user.token + "/" + projectData.id)
          .then(function onGetSuccess(response) {
            if (response.data.info && response.data.info.return_code == "1.6.1")
              $scope.projects.push({
                id: projectData.id,
                name: projectData.name,
                members: (response.data && Object.keys(response.data.data).length ? response.data.data.array : null)
              });
            else
              $scope.projects.push({ id: projectData.id, name: projectData.name, members: null });
          },
          function onGetGail(response) {
            $scope.projects.push({ id: projectData.id, name: projectData.name, members: null });
          });
        });
      }
    },
    function onGetFail(response) { });
  };

  // Routine definition
  // Get event types (depending on projects)
  var getEventTypes = function() {
    return $http.get($rootScope.api.url + "/event/gettypes/" + $rootScope.user.token)
      .then(function onGetSuccess(response) {
        $scope.calendar.types = [];

        if (response.data.info && response.data.info.return_code == "1.5.1") {
          var data = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
          for (var i = 0; i < data.length; ++i) {
            $scope.calendar.types.push({
              id: data[i].id,
              name: data[i].name
            });
          }
        }
      },
      function onGetFail(response) { });
  };

  // Routine definition
  // Get user month planning (based on given start date)
  var getUserPlanning = function(date) {
    return $http.get($rootScope.api.url + "/planning/getmonth/" + $rootScope.user.token + "/" + date)
    .then(function onGetSuccess(response) {
      $scope.calendar.events = [];
      $scope.content.onLoad = false;
      $scope.content.isValid = true;

      if (!$scope.content.isInit)
        $scope.content.isInit = true;
      if (response.data.info && response.data.info.return_code == "1.5.1") {      
        var data = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
        for (var i = 0; i < data.events.length; ++i) {
          $scope.calendar.events.push({
            id: data.events[i].id,
            projectId: (data.events[i].projectId ? data.events[i].projectId : null),
            projectName: (data.events[i].projectId ? $scope.projects[data.events[i].projectId].name + " | " : null),
            typeName: data.events[i].type.name + " | ",
            typeId: data.events[i].type.id,
            title: data.events[i].title,
            description: data.events[i].description,
            startsAt: moment(data.events[i].beginDate.date).toDate(),
            endsAt: moment(data.events[i].endDate.date).toDate(),
            draggable: false,
            resizable: false
          });
        }
      }
      else
        Notification.info({ message: "No events to display.", delay: 2500 });
    },
    function onGetFail(response) {
      $scope.calendar.events = [];
      $scope.content.onLoad = false;
      $scope.content.isValid = false;
    });
  };

  // START
  getUserProjectsWithMembers().then(function() {
    getEventTypes().then(function() {
      getUserPlanning(moment().startOf("month").format("YYYY-MM-DD"));
    });
  });



  /* ==================== REFRESH OBJECT (EVENT/TASK) ==================== */

  // "Previous/Today/Next" button handler
  $scope.method.onRefresh = function() {
    if ($scope.content.isInit) {
      Notification.info({ message: "Loading...", delay: 1500 });
      getUserPlanning(moment($scope.view.date).startOf("month").format("YYYY-MM-DD"));
    }
  };

  // Calendar mode (day/week/month/year) change watch
  $scope.$watch("view.mode", function() {
    $scope.method.onRefresh();
  });



  /* ==================== CREATE OBJECT (EVENT/TASK) ==================== */

  // "Create" button handler
  $scope.method.onEventCreate = function() {
    var modal_eventCreation = "";

    modal_eventCreation = $uibModal.open({ animation: true, templateUrl: "modal_eventUpdate.html", controller: "modal_eventUpdate", size: "lg",
      resolve: {
        projects: function() { return $scope.projects },
        eventTypes: function() { return $scope.calendar.types; },
        eventToEdit: function() { return null; }
      }
    });
    modal_eventCreation.result.then(function onCreateConfirm(data) {
      Notification.info({ message: "Loading...", delay: 1500 });
      $http.post($rootScope.api.url + "/event/postevent", {
        data: {
          token: $rootScope.user.token,
          projectId: (data.project ? data.project.id : null),
          title: data.title,
          description: data.description,
          begin: data.beginDate,
          end: data.endDate,
          typeId: data.type.id,
          icon: "DATA" // TEMP
        }})
      .then(function onCreateSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.5.1") {
          Notification.success({ message: "Event \"" + data.title + "\" successfully created.", delay: 2500 });
          $scope.method.onRefresh();
        }
        else { Notification.warning({ message: "Unable to create event \"" + data.title + "\". Please try again.", delay: 2500 }); }
      },
      function onCreateFail(response) { Notification.warning({ message: "Unable to create event \"" + data.title + "\". Please try again.", delay: 2500 }); })
    },
    function onCreateCancel() { Notification.success({ message: "Event creation cancelled.", delay: 2500 }); });
  };



  /* ==================== EDIT OBJECT (EVENT/TASK) ==================== */

  // "Edit" button handler
  $scope.method.onEventEdit = function(event) {
    var modal_eventEdition = "";

    modal_eventEdition = $uibModal.open({ animation: true, templateUrl: "modal_eventUpdate.html", controller: "modal_eventUpdate", size: "lg",
      resolve: {
        projects: function() { return $scope.projects },        
        eventTypes: function() { return $scope.calendar.types; },
        eventToEdit: function() { return event; }
      }
    });
    modal_eventEdition.result.then(function onEditConfirm(data) {
      Notification.info({ message: "Loading...", delay: 1500 });
      $http.put($rootScope.api.url + "/event/editevent", {
        data: {
          token: $rootScope.user.token,
          projectId: (data.project ? data.project.id : null),
          eventId: data.id,
          title: data.title,
          description: data.description,
          begin: moment(data.beginDate).format("YYYY-MM-DD HH:mm:ss"),
          end: moment(data.endDate).format("YYYY-MM-DD HH:mm:ss"),
          typeId: data.type.id,
          icon: "DATA" // TEMP
        }})
      .then(function onEditSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.5.1") {
          Notification.success({ message: "Event \"" + data.title + "\" successfully edited.", delay: 2500 });
          $scope.method.onRefresh();
        }
        else { Notification.warning({ message: "Unable to edit event \"" + data.title + "\". Please try again.", delay: 2500 }); }
      },
      function onEditFail(response) { Notification.warning({ message: "Unable to edit event \"" + data.title + "\". Please try again.", delay: 2500 }); })
    },
    function onEditCancel() { Notification.success({ message: "Event edition cancelled.", delay: 2500 }); });
  };



  /* ==================== DELETE OBJECT (EVENT/TASK) ==================== */

  // "Delete" button handler
  $scope.method.onEventDelete = function(event) {
    var modal_eventDeletion = "";

    modal_eventDeletion = $uibModal.open({ animation: true, templateUrl: "modal_eventDeletion.html", controller: "modal_eventDeletion" });
    modal_eventDeletion.result.then(function onDeleteConfirm() {
      $http.delete($rootScope.api.url + "/event/delevent/" + $rootScope.user.token + "/" + event.id)
      .then(function onDeleteSucess(response) {
        Notification.success({ message: "Event successfully deleted.", delay: 2500 });
        getUserPlanning($scope.view.mode == "year" ? moment().startOf("year").format("YYYY-MM-DD") : moment().startOf("month").format("YYYY-MM-DD"));
      }, 
      function onDeleteFail(response) { Notification.warning({ message: "Unable to delete selected event. Please try again.", delay: 2500 }); });
    },
    function onDeleteCancel() { });
  };

}]);



/**
* Controller definition (from view)
* EVENT CREATION/EDITION => parameters
*
*/
app.controller("modal_eventUpdate", ["$scope", "modalInputService", "$uibModalInstance", "$http", "$rootScope", "projects", "eventTypes", "eventToEdit",
  function($scope, modalInputService, $uibModalInstance, $http, $rootScope, projects, eventTypes, eventToEdit) {

  // Scope variables initialization
  console.log(JSON.stringify(projects));

  $scope.projects = projects;
  $scope.members = [];

  $scope.update = { id: "", newTitle: "", newDescription: "", newBeginDate: "", newEndDate: "", newType: "", newProject: "", newMembers: [] };
  $scope.events = { types: [] };
  $scope.events.types = eventTypes;

  $scope.method = { onProjectUpdate: "", onConfirm: "", onCancel: "" };

  // Event data setting
  // ONLY FOR EDITION/NOT CREATION MODE
  if (eventToEdit) {
    $scope.update.id = eventToEdit.id;
    $scope.update.newTitle = eventToEdit.title;
    $scope.update.newDescription = eventToEdit.description;
    $scope.update.newBeginDate = moment(eventToEdit.startsAt).toDate();
    $scope.update.newEndDate = moment(eventToEdit.endsAt).toDate();
    $scope.update.newType = $scope.events.types[eventToEdit.typeId];

    if (eventToEdit.projectId)
      $scope.update.newProject = $scope.projects[eventToEdit.projectId];
    else
      $scope.update.newProject = "";

    console.log("DETECTED: " + JSON.stringify($scope.update));

    $http.get($rootScope.api.url + "/event/getevent/" + $rootScope.user.token + "/" + eventToEdit.id)
    .then(function onGetSuccess(response) {
      if (response.data.info && response.data.info.return_code == "1.5.1") {
        var users = (Object.keys(response.data.data).length && Object.keys(response.data.data.users).length ? response.data.data.users : null);
        for (var i = 0; i < users.length; ++i)
          $scope.update.newMembers.push({ id: users[i].id, name: users[i].name });
      }
      else
        $scope.update.newMembers = "";
    },
    function onGetFail(response) {
      $scope.update.newMembers = "";
    });
  }

  // Project selection change handler
  $scope.method.onProjectUpdate = function(selectedProject) {
    $scope.members = [];

    if (selectedProject && selectedProject.members)
      for (var i = 0; i < selectedProject.members.length; ++i)
        $scope.members.push({ id: selectedProject.members[i].id, name: selectedProject.members[i].firstname + " " + selectedProject.members[i].lastname });
  };

  // "Ok" button handler
  $scope.method.onConfirm = function() {
    var newElement = { title: "", description: "", beginDate: "", endDate: "", type: "", project: "", members: "" };

    // New event title
    if ($scope.update.newTitle && $scope.update.newTitle !== "")
      newElement.title = $scope.update.newTitle;
    else
      angular.element(document.querySelector("#modal_newTitle")).attr("class", "input-error");

    // New event description
    if ($scope.update.newDescription && $scope.update.newDescription !== "")
      newElement.description = $scope.update.newDescription;
    else
      angular.element(document.querySelector("#modal_newDescription")).attr("class", "input-error");

    // New event begin date
    if ($scope.update.newBeginDate && $scope.update.newBeginDate !== "")
      newElement.beginDate = $scope.update.newBeginDate;
    else
      angular.element(document.querySelector("#modal_newBeginDate")).attr("class", "input-error");

    // New event end date
    if ($scope.update.newEndDate && $scope.update.newEndDate !== "")
      newElement.endDate = $scope.update.newEndDate;
    else
      angular.element(document.querySelector("#modal_newEndDate")).attr("class", "input-error");

    // New event type
    if ($scope.update.newType && $scope.update.newType !== "")
      newElement.type = $scope.update.newType;
    else
      angular.element(document.querySelector("#modal_newType")).attr("class", "input-error");

    // New project ID
    if ($scope.update.newProject && $scope.update.newProject !== "")
      newElement.project = $scope.update.newProject;

    // IS THIS OPERATION CREATION OR EDITION?
    // Does event already have an ID?
    if ($scope.update.id)
      newElement.id = $scope.update.id;

    if (newElement.title && newElement.description && newElement.beginDate && newElement.endDate && newElement.type) {
      console.log("SENT: " + JSON.stringify(newElement));
      $uibModalInstance.close(newElement);
    }
  };

  // "Cancel" button handler
  $scope.method.onCancel = function() {
    $uibModalInstance.dismiss();
  };
}]);



/**
* Controller definition (from view)
* EVENT DELETION => confirmation?
*
*/
app.controller("modal_eventDeletion", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  // Scope variables initialization
  $scope.method = { onConfirm: "", onCancel: "" };

  $scope.method.onConfirm = function() { $uibModalInstance.close(); };
  $scope.method.onCancel = function() { $uibModalInstance.dismiss(); };
}]);