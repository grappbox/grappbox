/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Module configuation
* APP calendar page content
*
*/
app.config(["calendarConfig", function(calendarConfig) {
  calendarConfig.dateFormatter = 'moment';
  calendarConfig.allDateFormats.moment.date.hour = 'HH:mm';
  calendarConfig.allDateFormats.moment.title.day = 'ddd D MMM';
  calendarConfig.displayAllMonthEvents = true;
  calendarConfig.displayEventEndTimes = true;
  calendarConfig.showTimesOnWeekView = true;
}]);



/**
* Controller definition
* APP calendar page content
*
*/
app.controller('calendarController', ["$scope", "Notification", "$http", "$rootScope", "$cookies", "moment", "$uibModal", function($scope, Notification, $http, $rootScope, $cookies, moment, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.data = { onLoad: true, calendar: "", isValid: true };
  $scope.events = [];

  $scope.view_calendarTitle = "";
  $scope.view_calendarMode = 'month';
  $scope.view_currentDate = new Date();
  $scope.view_isCellOpen = true;



  /* ==================== START ==================== */

  // Routine definition
  // Get user's month planning (based on given start date)
  var getDatePlanning = function(date) {
    $http.get($rootScope.apiBaseURL + "/planning/getmonth/" + $cookies.get("USERTOKEN") + "/" + date)
      .then(function userCalendarReceived(response) {
        $scope.data.calendar = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
        $scope.data.isValid = true;
        $scope.data.onLoad = false;
        $scope.events = [];

        for (var i = 0; i < $scope.data.calendar.events.length; ++i) {      
          $scope.events.push({
            id: $scope.data.calendar.events[i].id,
            projectId: $scope.data.calendar.events[i].projectId,
            type: 'event',
            tag: '[ Event ]',
            title: $scope.data.calendar.events[i].title,
            description: $scope.data.calendar.events[i].description,
            startsAt: moment($scope.data.calendar.events[i].beginDate.date).toDate(),
            endsAt: moment($scope.data.calendar.events[i].endDate.date).toDate(),
            draggable: false,
            resizable: false
          });
        }
    },
    function userCalendarNotReceived(response) {
      $scope.data.calendar = null;
      $scope.data.isValid = false;
      $scope.data.onLoad = false;
      $scope.events = [];
    });
  }

  // START
  getDatePlanning(moment().startOf('month').format('YYYY-MM-DD'));



  /* ==================== REFRESH OBJECT (EVENT/TASK) ==================== */

  // Routine definition
  // Refresh event list (based on given start date)
  var local_refreshEventList = function() {
    Notification.info({ message: "Loading...", delay: 5000 });    
    getDatePlanning(moment($scope.view_currentDate).startOf('month').format('YYYY-MM-DD'));
  };

  // "Previous/Today/Next" button handler
  $scope.view_updateEventList = function() {
    local_refreshEventList();
  };

  // Calendar mode (day/week/month/year) change watch
  $scope.$watch('view_calendarMode', function() {
    local_refreshEventList();
  });



  /* ==================== CREATE OBJECT (EVENT/TASK) ==================== */

  // "Create" button handler
  $scope.view_onCreateEvent = function(event) {
    var modalInstance_createEvent = "";

    modalInstance_createEvent = $uibModal.open({ animation: true, templateUrl: "view_createEvent.html", controller: "view_createEvent", size: "lg" });
    modalInstance_createEvent.result.then(function creationConfirmed(data) {
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
      .then(function eventCreationSuccess(response) {
        if (response.data.info && response.data.info.return_code == "1.5.1") {
          Notification.success({ message: "Event \"" + data.title + "\" successfully created.", delay: 5000 });
          local_refreshEventList();
        }
        else { Notification.warning({ message: "Unable to create \"" + data.title + "\" event. Please try again.", delay: 5000 }); }
      },
      function eventCreationFailed(response) { Notification.warning({ message: "Unable to create \"" + data.title + "\" event. Please try again.", delay: 5000 }); })
    },
    function creationNotConfirmed() { Notification.success({ message: "Event creation cancelled.", delay: 5000 }); });
  };



  /* ==================== DELETE OBJECT (EVENT/TASK) ==================== */

  // "Delete" button handler
  $scope.view_onDeleteEvent = function(event) {
    var modalInstance_deleteEvent = "";

    modalInstance_deleteEvent = $uibModal.open({ animation: true, templateUrl: "view_deleteEvent.html", controller: "view_deleteEvent" });
    modalInstance_deleteEvent.result.then(function deletionConfirmed() {
      $http.delete($rootScope.apiBaseURL + "/event/delevent/" + $cookies.get("USERTOKEN") + "/" + event.id)
      .then(function deletionSuccess(response) {
        Notification.success({ message: "Event successfully deleted.", delay: 5000 });
        getDatePlanning($scope.view_calendarMode == 'year' ? moment().startOf('year').format('YYYY-MM-DD') : moment().startOf('month').format('YYYY-MM-DD'));
      }, 
      function deletionFailure(response) { Notification.warning({ message: "Unable to delete the selected event. Please try again.", delay: 5000 }); });
    },
    function deletionNotConfirmed() { });
  };



  /* ==================== <waiting> ==================== */

  $scope.eventClicked = function(event) {
    console.log('Clicked', event);
  };

  $scope.eventEdited = function(event) {
    console.log('Edited', event);
  };

  $scope.eventTimesChanged = function(event) {
    console.log('Dropped or resized', event);
  };

  $scope.toggle = function($event, field, event) {
    $event.preventDefault();
    $event.stopPropagation();
    event[field] = !event[field];
  };

}]);



/**
* Controller definition (from view)
* EVENT CREATION => parameters
*
*/
app.controller("view_createEvent", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  $scope.view_createEventSuccess = function() {
    var newElement = { title: "", description: "", beginDate: "", endDate: "" };
    var data = "";

    data = angular.element(document.querySelector("#view_newEventTitle"));
    if (data && data.val() !== "")
      newElement.title = data.val();
    else
      angular.element(data).attr("class", "input-error");

    data = angular.element(document.querySelector("#view_newEventDescription"));
    if (data && data.val() !== "")
      newElement.description = data.val();
    else
      angular.element(data).attr("class", "input-error");

    data = angular.element(document.querySelector("#view_newEventBeginDate"));
    if (data && data.val() !== "")
      newElement.beginDate = data.val();
    else
      angular.element(data).attr("class", "input-error");

    data = angular.element(document.querySelector("#view_newEventEndDate"));
    if (data && data.val() !== "")
      newElement.endDate = data.val();
    else
      angular.element(data).attr("class", "input-error");
 
    if (newElement.title && newElement.description && newElement.beginDate && newElement.endDate)
      $uibModalInstance.close(newElement);
  };
  $scope.view_createEventFailure = function() { $uibModalInstance.dismiss(); };
}]);



/**
* Controller definition (from view)
* EVENT DELETION => confirmation?
*
*/
app.controller("view_deleteEvent", ["$scope", "modalInputService", "$uibModalInstance", function($scope, modalInputService, $uibModalInstance) {

  $scope.view_deleteEventSuccess = function() { $uibModalInstance.close(); };
  $scope.view_deleteEventFailure = function() { $uibModalInstance.dismiss(); };
}]);