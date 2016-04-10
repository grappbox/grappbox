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
app.controller("calendarController", ["$scope", "Notification", "$http", "$rootScope", "$cookies", "moment", "$uibModal", function($scope, Notification, $http, $rootScope, $cookies, moment, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.content = { onLoad: true, isValid: true };
  $scope.calendar = { data: [], events: [] };
  $scope.view = { title: "", mode: "month", date: new Date(), isCellOpen: true };
  $scope.method = { onRefresh: "", onEventCreate: "", onEventEdit: "", onEventDelete: "" };

  /* ==================== START ==================== */

  // Routine definition
  // Get user"s month planning (based on given start date)
  var getUserPlanning = function(date) {
    $http.get($rootScope.apiBaseURL + "/planning/getmonth/" + $cookies.get("USERTOKEN") + "/" + date)
      .then(function onGetSuccess(response) {
        $scope.calendar.data = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
        $scope.calendar.events = [];
        $scope.content.onLoad = false;
        $scope.content.isValid = true;

        for (var i = 0; i < $scope.calendar.data.events.length; ++i) {      
          $scope.calendar.events.push({
            id: $scope.calendar.data.events[i].id,
            projectId: $scope.calendar.data.events[i].projectId,
            type: "event",
            tag: "[ Event ]",
            title: $scope.calendar.data.events[i].title,
            description: $scope.calendar.data.events[i].description,
            startsAt: moment($scope.calendar.data.events[i].beginDate.date).toDate(),
            endsAt: moment($scope.calendar.data.events[i].endDate.date).toDate(),
            draggable: false,
            resizable: false
          });
        }
    },
    function onGetFail(response) {
      $scope.calendar.data = null;
      $scope.calendar.events = [];
      $scope.content.onLoad = false;
      $scope.content.isValid = false;
    });
  }

  // START
  getUserPlanning(moment().startOf("month").format("YYYY-MM-DD"));



  /* ==================== REFRESH OBJECT (EVENT/TASK) ==================== */

  // "Previous/Today/Next" button handler
  $scope.method.onRefresh = function() {
    Notification.info({ message: "Loading...", delay: 5000 });    
    getUserPlanning(moment($scope.view.date).startOf("month").format("YYYY-MM-DD"));
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
      resolve: { selectedEvent: function() { return null; }} });
    modal_eventCreation.result.then(function onCreateConfirm(data) {
      Notification.info({ message: "Loading...", delay: 5000 });
      $http.post($rootScope.apiBaseURL + "/event/postevent", {
        data: {
          token: $cookies.get("USERTOKEN"),
          title: data.title,
          description: data.description,
          begin: data.beginDate,
          end: data.endDate,
          icon: "DATA", // TEMP
          typeId:  1 // TEMP
        }})
      .then(function onCreateSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.5.1") {
          Notification.success({ message: "Event \"" + data.title + "\" successfully created.", delay: 5000 });
          $scope.method.onRefresh();
        }
        else { Notification.warning({ message: "Unable to create event \"" + data.title + "\". Please try again.", delay: 5000 }); }
      },
      function onCreateFail(response) { Notification.warning({ message: "Unable to create event \"" + data.title + "\". Please try again.", delay: 5000 }); })
    },
    function onCreateCancel() { Notification.success({ message: "Event creation cancelled.", delay: 5000 }); });
  };



  /* ==================== EDIT OBJECT (EVENT/TASK) ==================== */

  // "Edit" button handler
  $scope.method.onEventEdit = function(event) {
    var modal_eventEdition = "";

    modal_eventEdition = $uibModal.open({ animation: true, templateUrl: "modal_eventUpdate.html", controller: "modal_eventUpdate", size: "lg",
      resolve: { selectedEvent: function() { return event; }} });
    modal_eventEdition.result.then(function onEditConfirm(data) {
      Notification.info({ message: "Loading...", delay: 5000 });
      $http.post($rootScope.apiBaseURL + "/event/postevent", {
        data: {
          token: $cookies.get("USERTOKEN"),
          title: data.title,
          description: data.description,
          begin: data.beginDate,
          end: data.endDate,
          icon: "DATA", // TEMP
          typeId:  1 // TEMP
        }})
      .then(function onEditSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.5.1") {
          Notification.success({ message: "Event \"" + data.title + "\" successfully edited.", delay: 5000 });
          $scope.method.onRefresh();
        }
        else { Notification.warning({ message: "Unable to edit event \"" + data.title + "\". Please try again.", delay: 5000 }); }
      },
      function onEditFail(response) { Notification.warning({ message: "Unable to edit event \"" + data.title + "\". Please try again.", delay: 5000 }); })
    },
    function onEditCancel() { Notification.success({ message: "Event edition cancelled.", delay: 5000 }); });
  };



  /* ==================== DELETE OBJECT (EVENT/TASK) ==================== */

  // "Delete" button handler
  $scope.method.onEventDelete = function(event) {
    var modal_eventDeletion = "";

    modal_eventDeletion = $uibModal.open({ animation: true, templateUrl: "modal_eventDeletion.html", controller: "modal_eventDeletion" });
    modal_eventDeletion.result.then(function onDeleteConfirm() {
      $http.delete($rootScope.apiBaseURL + "/event/delevent/" + $cookies.get("USERTOKEN") + "/" + event.id)
      .then(function onDeleteSucess(response) {
        Notification.success({ message: "Event successfully deleted.", delay: 5000 });
        getUserPlanning($scope.view.mode == "year" ? moment().startOf("year").format("YYYY-MM-DD") : moment().startOf("month").format("YYYY-MM-DD"));
      }, 
      function onDeleteFail(response) { Notification.warning({ message: "Unable to delete selected event. Please try again.", delay: 5000 }); });
    },
    function onDeleteCancel() { });
  };

}]);



/**
* Controller definition (from view)
* EVENT CREATION/EDITION => parameters
*
*/
app.controller("modal_eventUpdate", ["$scope", "modalInputService", "$uibModalInstance", "selectedEvent", function($scope, modalInputService, $uibModalInstance, selectedEvent) {

  // Scope variables initialization
  $scope.update = { onConfirm: "", onCancel: "", eventTitle: "", eventDescription: "", eventBeginDate: "", eventEndDate: "" };

  if (selectedEvent) {
    $scope.update.eventTitle = selectedEvent.title;
    $scope.update.eventDescription = selectedEvent.description;
    $scope.update.eventBeginDate = selectedEvent.startsAt;
    $scope.update.eventEndDate = selectedEvent.endsAt;
  }

  $scope.update.onConfirm = function() {
    var newElement = { title: "", description: "", beginDate: "", endDate: "" };

    if ($scope.update.eventTitle && $scope.update.eventTitle !== "")
      newElement.title = $scope.update.eventTitle;
    else
      angular.element(document.querySelector("#modal_eventTitle")).attr("class", "input-error");

    if ($scope.update.eventDescription && $scope.update.eventDescription !== "")
      newElement.description = $scope.update.eventDescription;
    else
      angular.element(document.querySelector("#modal_eventDescription")).attr("class", "input-error");

    if ($scope.update.eventBeginDate && $scope.update.eventBeginDate !== "")
      newElement.beginDate = $scope.update.eventBeginDate;
    else
      angular.element(document.querySelector("#modal_eventBeginDate")).attr("class", "input-error");

    if ($scope.update.eventEndDate && $scope.update.eventEndDate !== "")
      newElement.endDate = $scope.update.eventEndDate;
    else
      angular.element(document.querySelector("#modal_eventEndDate")).attr("class", "input-error");

    if (newElement.title && newElement.description && newElement.beginDate && newElement.endDate)
      $uibModalInstance.close(newElement);
  };

  $scope.update.onCancel = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* EVENT DELETION => confirmation?
*
*/
app.controller("modal_eventDeletion", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  // Scope variables initialization
  $scope.delete = { onConfirm: "", onCancel: "" };

  $scope.delete.onConfirm = function() { $uibModalInstance.close(); };
  $scope.delete.onCancel = function() { $uibModalInstance.dismiss(); };
}]);