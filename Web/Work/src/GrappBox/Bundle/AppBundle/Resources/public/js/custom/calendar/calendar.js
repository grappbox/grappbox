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
app.controller('calendarController', ["$scope", "$http", "$rootScope", "$cookies", "moment", function($scope, $http, $rootScope, $cookies, moment) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.data = { onLoad: true, calendar: "", isValid: true };
  $scope.events = [];

  // Get user's current month planning
  $http.get($rootScope.apiBaseURL + "/planning/getmonth/" + $cookies.get("USERTOKEN") + "/" + moment().format('YYYY-MM-DD'))
    .then(function userCalendarReceived(response) {
      $scope.data.calendar = (response.data && Object.keys(response.data.data).length ? response.data.data.array : null);
      $scope.data.isValid = true;
      $scope.data.onLoad = false;

      console.log($scope.data.calendar);
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
/*      for (var i = 0; i < $scope.data.calendar.tasks.length; ++i) {      
        $scope.events.push({
          id: $scope.data.calendar.tasks[i].id,
          projectId: $scope.data.calendar.tasks[i].projectId,
          type: 'task',
          tag: '[ Task ]',
          title: $scope.data.calendar.tasks[i].title,
          description: $scope.data.calendar.tasks[i].description,
          startsAt: moment($scope.data.calendar.tasks[i].startedAt.date).toDate(),
          endsAt: ($scope.data.calendar.tasks[i].finishedAt ? moment($scope.data.calendar.tasks[i].finishedAt.date).toDate() : moment().toDate()),
          dueDate: moment($scope.data.calendar.tasks[i].dueDate.date).toDate(),
          draggable: false,
          resizable: false
        });
      }*/
    },
    function userCalendarNotReceived(response) {
      $scope.data.calendar = null;
      $scope.data.isValid = false;
      $scope.data.onLoad = false;
      $scope.events = [];
    });

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

}]);