/*
Summary: Calendar Controller
*/

angular.module('GrappBox.controllers')

.controller('CalendarCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, moment, calendarConfig, Planning) {

  $scope.$on('$ionicView.beforeEnter', function () {
    $rootScope.viewColor = $rootScope.GBNavColors.calendar;
  });

  //Refresher
  $scope.doRefresh = function () {
    $scope.calendar.events = [];
    //$scope.calendar.date = new Date();
    $scope.GetEvents();
    console.log("View refreshed !");
  }

  $scope.projectId = $stateParams.projectId;

  // Bind current date time so we can actualize from an external function before calling API
  /*$scope.setTodayDateTime = function () {
    var currentDate = new Date();
    var second = currentDate.getSeconds();
    var minute = currentDate.getMinutes();
    var hour = currentDate.getHours();
    var day = currentDate.getDate();
    var month = currentDate.getMonth() + 1; // + 1 because it starts from 0
    var year = currentDate.getFullYear();
    // Add a '0' if days are less than 10
    if (day < 10) {
      day = '0' + day;
    }
    // Same for months
    if (month < 10) {
      month = '0' + month;
    }
    // Same for hour
    if (hour < 10) {
      hour = '0' + hour;
    }
    $scope.todayDateTime = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
    console.log(currentDate);
  }
  $scope.setTodayDateTime();*/

  // Set current month from which month we are in calendar
/*  $scope.setCurrentYearMonth = function() {
    $scope.currentMonth = moment(calendar.date).month() + 1;
    if ($scope.currentMonth < 10) {
      $scope.currentMonth = '0' + $scope.currentMonth;
    }
    $scope.currentYear = moment(calendar.date).year();
  }*/

  // Bind first day of current month for Planning API calls
  /*$scope.setFirstDayOfMonth = function () {
    var date = new Date();
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    if (month < 10) {
      month = '0' + month;
    }
    $scope.setCurrentYearMonth();
    $scope.firstDayOfMonth = $scope.currentYear + '-' + $scope.currentMonth + '-' + '01';
  }*/

  $scope.calendar = {};
  $scope.calendar.typeView = 'month';
  $scope.calendar.date = new Date();
  $scope.calendar.cellOpen = true;

  $scope.eventClicked = function(event) {
    $state.go('app.event', { projectId: $scope.projectId, eventId: event.eventAPI.id });
    console.log(event);
  }

  // $scope.calendar.cellEdit = function (cell) {
  //     //console.log(cell);
  //     if (cell.inYear) {
  //         cell.cssClass = 'odd-cell';
  //     }
  // };

  /*
  ** Get month planning
  ** Method: GET
  */
  $scope.events = [];
  $scope.GetEvents = function () {
    //$scope.setFirstDayOfMonth();
    console.log(moment($scope.calendar.date).startOf("month").format("YYYY-MM-DD"));
    Planning.Month().get({
      date: moment($scope.calendar.date).startOf("month").format("YYYY-MM-DD")
    }).$promise
    .then(function (data) {
      $scope.events = data.data.array.events;
      console.log('Get month planning successful !');
      console.log(data.data.array.events);
      // Push all events to events array to be understood from calendar lib
      if ($scope.events) {
        for (var i = 0; i < $scope.events.length; i++) {
          $scope.calendar.events.push({
            title: $scope.events[i].title,
            startsAt: moment($scope.events[i].beginDate).toDate(),
            endsAt: moment($scope.events[i].endDate).toDate(),
            color: { primary: '#e3bc08', secondary: '#fdf1ba' },
            draggable: false,
            resizable: false,
            eventAPI: $scope.events[i]
          });
        }
      }
    })
    .catch(function (error) {
      console.error('Get month planning failed ! Reason: ' + error.status + ' ' + error.statusText);
      Toast.show("Get month planning failed");
      console.error(error);
      //$rootScope.hideLoading();
    })
    .finally(function() {
      $scope.$broadcast('scroll.refreshComplete');
    })
  }
  $scope.GetEvents();

  // $scope.calendar.events = [{
  //   title: 'My event title', // The title of the event
  //   startsAt: moment("2016-11-10 09:00:00").toDate(),
  //   endsAt: moment("2016-11-10 23:00:00").toDate(), // A javascript date object for when the event starts
  //   color: { // can also be calendarConfig.colorTypes.warning for shortcuts to the deprecated event types
  //     primary: '#e3bc08', // the primary event color (should be darker than secondary)
  //     secondary: '#fdf1ba' // the secondary event color (should be lighter than primary)
  //   },
  //   actions: [{ // an array of actions that will be displayed next to the event title
  //     label: '<i class=\'glyphicon glyphicon-pencil\'></i>', // the label of the action
  //     cssClass: 'edit-action', // a CSS class that will be added to the action element so you can implement custom styling
  //     onClick: function (args) { // the action that occurs when it is clicked. The first argument will be an object containing the parent event
  //       console.log('Edit event', args.calendarEvent);
  //     }
  //   }],
  //   draggable: false, //Allow an event to be dragged and dropped
  //   resizable: false, //Allow an event to be resizable
  //   //incrementsBadgeTotal: true, //If set to false then will not count towards the badge total amount on the month and year view
  //   //recursOn: 'year', // If set the event will recur on the given period. Valid values are year or month
  //   //cssClass: 'a-css-class-name', //A CSS class (or more, just separate with spaces) that will be added to the event when it is displayed on each view. Useful for marking an event as selected / active etc
  //   //allDay: false // set to true to display the event as an all day event on the day view
  // }];
})
