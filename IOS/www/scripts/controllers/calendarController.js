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
        $scope.GetMonthPlanning();
        console.log("View refreshed !");
    }

    $scope.projectId = $stateParams.projectId;

    $scope.calendar = {};
    $scope.calendar.typeView = 'month';
    $scope.calendar.date = new Date();
    $scope.calendar.cellOpen = true;
    $scope.calendar.cellEdit = function (cell) {
        //console.log(cell);
        if (cell.inYear) {
            cell.cssClass = 'odd-cell';
        }
    };

    /*
    ** Get month planning
    ** Method: GET
    */
    $scope.events = [];
    $scope.GetEvents = function () {
        Planning.Month().get({
            date: "2016-10-01"
        }).$promise
            .then(function (data) {
                $scope.events = data.data.array.events;
                console.log('Get month planning successful !');
                console.log(data.data.array.events);
                $scope.calendar.events.push({
                    title: $scope.events[0].title,
                    startsAt: moment($scope.events[0].beginDate).toDate(),
                    endsAt: moment($scope.events[0].endDate).toDate(),
                    color: { primary: '#e3bc08', secondary: '#fdf1ba' }
                });
            })
            .catch(function (error) {
                console.error('Get month planning failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }
    $scope.GetEvents();

    $scope.calendar.events = [{
      title: 'My event title', // The title of the event
      startsAt: moment("2016-08-14 09:00:00").toDate(),
      endsAt: moment("2016-08-14 23:00:00").toDate(), // A javascript date object for when the event starts
      color: { // can also be calendarConfig.colorTypes.warning for shortcuts to the deprecated event types
          primary: '#e3bc08', // the primary event color (should be darker than secondary)
          secondary: '#fdf1ba' // the secondary event color (should be lighter than primary)
      },
      actions: [{ // an array of actions that will be displayed next to the event title
          label: '<i class=\'glyphicon glyphicon-pencil\'></i>', // the label of the action
          cssClass: 'edit-action', // a CSS class that will be added to the action element so you can implement custom styling
          onClick: function (args) { // the action that occurs when it is clicked. The first argument will be an object containing the parent event
              console.log('Edit event', args.calendarEvent);
          }
      }],
      draggable: false, //Allow an event to be dragged and dropped
      resizable: false, //Allow an event to be resizable
      //incrementsBadgeTotal: true, //If set to false then will not count towards the badge total amount on the month and year view
      //recursOn: 'year', // If set the event will recur on the given period. Valid values are year or month
      //cssClass: 'a-css-class-name', //A CSS class (or more, just separate with spaces) that will be added to the event when it is displayed on each view. Useful for marking an event as selected / active etc
      //allDay: false // set to true to display the event as an all day event on the day view
  }];
})