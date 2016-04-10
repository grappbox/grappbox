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
app.controller('calendarController', ["$scope", "moment", function($scope, moment) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.data = { onLoad: false, calendar: true, isValid: true };

  $scope.calendarTitle = "";
  $scope.calendarView = 'month';
  $scope.viewDate = new Date();
  $scope.isCellOpen = true;

  $scope.eventClicked = function(event) {
    console.log('Clicked', event);
  };

  $scope.eventEdited = function(event) {
    console.log('Edited', event);
  };

  $scope.eventDeleted = function(event) {
    console.log('Deleted', event);
  };

  $scope.eventTimesChanged = function(event) {
    console.log('Dropped or resized', event);
  };

  $scope.toggle = function($event, field, event) {
    $event.preventDefault();
    $event.stopPropagation();
    event[field] = !event[field];
  };


  // DUMMY DATA [TEMP]
  $scope.events = [
    {
      title: 'Event 1',
      startsAt: new Date(2016,2,26,14,30),
      endsAt: new Date(2016,2,26,15),
      draggable: true,
      resizable: true
    }
  ];

}]);