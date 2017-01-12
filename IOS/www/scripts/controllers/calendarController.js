/*
Summary: Calendar Controller
*/

angular.module('GrappBox.controllers')

.controller('CalendarCtrl', function ($ionicPlatform, $scope, $rootScope, $state, $stateParams, moment, calendarConfig, Toast, Planning) {

  // Set the good color to Ionic nav bar
  $scope.$on('$ionicView.beforeEnter', function () {
    $rootScope.viewColor = $rootScope.GBNavColors.calendar;
  });

  // Refresher
  $scope.doRefresh = function () {
    $scope.calendar.events = [];
    $scope.GetEvents();
    console.log("View refreshed !");
  }

  // Get project Id sent from previous view param
  $scope.projectId = $stateParams.projectId;

  //
  $scope.calendar = {};
  $scope.calendar.typeView = 'month';
  $scope.calendar.date = new Date();
  $scope.calendar.cellOpen = true;

  // Go to event clicked view
  $scope.eventClicked = function(event) {
    $state.go('app.event', { projectId: $scope.projectId, eventId: event.eventAPI.id });
    console.log(event);
  }

  /*
  ** Get month planning
  ** Method: GET
  */
  $scope.events = [];
  $scope.GetEvents = function () {
    console.log(moment($scope.calendar.date).startOf("month").format("YYYY-MM-DD"));
    // Send the first day of the month to API
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
    })
    .finally(function() {
      $scope.$broadcast('scroll.refreshComplete');
    })
  }
  $scope.GetEvents();
})
