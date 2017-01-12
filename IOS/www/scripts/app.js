/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'ngCordova', 'GrappBox.controllers', 'GrappBox.api', 'GrappBox.directives', 'GrappBox.factories'])

// on starting
.run(function ($ionicPlatform, $rootScope, $ionicLoading, $http, $state, $stateParams) {
  $ionicPlatform.ready(function() {
    if(window.cordova && window.cordova.plugins.Keyboard) {
      // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
      // for form inputs)
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);

      // Don't remove this line unless you know what you are doing. It stops the viewport
      // from snapping when text inputs are focused. Ionic handles this internally for
      // a much nicer keyboard experience.
      cordova.plugins.Keyboard.disableScroll(true);
    }
    if(window.StatusBar) {
      StatusBar.styleDefault();
    }
  })

  // For debugging purpose (with <pre> display state current state etc)
  $rootScope.$state = $state;
  $rootScope.$stateParams = $stateParams;

    $rootScope.API_VERSION = '0.3'; //actual API's version
    //$rootScope.API = 'https://api.grappbox.com/app_dev.php/V' + $rootScope.API_VERSION + '/'; //API full link for controllers
    $rootScope.API = 'https://api.grappbox.com/' + $rootScope.API_VERSION + '/';

    $rootScope.userDatas = {
        id: "",
        firstname: "",
        lastname: "",
        email: "",
        avatar: "",
        is_client: "",
        token: ""
    }

    $rootScope.hasProject = false;

    $rootScope.showLoading = function () {
        $ionicLoading.show({
            template: '<p>Loading...</p><ion-spinner></ion-spinner>', noBackdrop: true
        });
    };

    $rootScope.hideLoading = function () {
        $ionicLoading.hide();
    };

    $rootScope.isBase64 = function (str) {
        try {
            window.atob(str);
        } catch (e) {
            return false;
        }
    }

    $rootScope.dropUnchangedFields = function (data, headerGetter) {

        var newData = angular.copy(data);
        for (key in newData.data) {
            if (!newData.data[key] && key != "logo")
                delete newData.data[key];
        }
        return angular.toJson(newData);

    }

    $rootScope.GBNavColors = {
        dashboard: "#FC575E",
        cloud: "#F1C40F",
        calendar: "#44BBFF",
        bugtracker: "#9E58DC",
        timeline: "#FF9F55",
        whiteboard: "#27AE60"
    }
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider, $httpProvider, calendarConfig) {

    //$ionicConfigProvider.views.maxCache(0);
    //$ionicConfigProvider.views.swipeBackEnabled(false);
    $ionicConfigProvider.views.transition('platform');          // transition between views
    $ionicConfigProvider.backButton.icon('ion-ios-arrow-back'); // iOS back icon
    $ionicConfigProvider.backButton.text('');                   // default is 'Back'
    $ionicConfigProvider.backButton.previousTitleText(false);   // hides the 'Back' text

    //Calendar
    calendarConfig.dateFormatter = "moment";
    calendarConfig.allDateFormats.moment.date.hour = "HH:mm";
    calendarConfig.allDateFormats.moment.date.weekDay = "ddd";
    calendarConfig.allDateFormats.moment.title.day = "ddd D MMM";
    calendarConfig.displayAllMonthEvents = true;
    calendarConfig.displayEventEndTimes = true;
    calendarConfig.showTimesOnWeekView = true;

    $stateProvider

    /*
    ** AUTHENTIFICATION
    */
    // authentification view
    .state('auth', {
        url: "/auth", //'url' means the rooting of the app as it would be on a web page in URL, we define hand-written
        templateUrl: "views/auth.html", //'templateUrl' is where app will search for the "physical" page
        controller: 'AuthCtrl' //link to controller
    })

    /*
    ** ABSTRACT
    */
    // entering application
    .state('app', {
        url: "/app",
        abstract: true, //'abstract' means will never render, but pages will inherit of it
        templateUrl: "views/app.html",
        controller: 'MenuCtrl'
    })

    /*
    ** PROJECTS LIST
    */
    // projects list view
    .state('app.projects', {
        url: "/projects",
        views: { //here we define the views inheritance
            'menuList': { //inherites from 'menuList' in app.html (<ion-nav-view name="menuList" [...]</ion-nav-view>)
                templateUrl: "views/projects.html",
                controller: 'ProjectsListCtrl'
            },
        },
        cache: false
    })


    /*
    ** DASHBOARD
    */
    // dashboard with Team Occupation, Next Meetings
    .state('app.dashboard', {
        url: "/projects/:projectId/dashboard",
        views: {
            'menuList': {
                templateUrl: "views/dashboard.html",
                controller: 'DashboardCtrl',
            }
        },
        cache: false
    })

    /*
    ** PROJECT
    */
    // single project view
    .state('app.project', {
        url: "/projects/:projectId",
        views: {
            'menuList': {
                templateUrl: "views/project.html",
                controller: 'ProjectCtrl'
            }
        },
        cache: false
    })

    // create project view
    .state('app.createProject', {
        url: "/projects/createProject",
        views: {
            'menuList': {
                templateUrl: "views/createProject.html",
                controller: 'CreateProjectCtrl'
            }
        }
    })

    // edit project view
    .state('app.editProject', {
        url: "/projects/:projectId/edit",
        views: {
            'menuList': {
                templateUrl: "views/editProject.html",
                controller: 'EditProjectCtrl'
            }
        }
    })

    /*
    ** PROJECTS ROLES
    */
    // create role view
    .state('app.createRole', {
        url: "/projects/:projectId/roles/add",
        views: {
            'menuList': {
                templateUrl: "views/createRole.html",
                controller: 'CreateRoleCtrl'
            }
        }
    })

    // role & users on role view
    .state('app.role', {
        url: "/projects/:projectId/roles/:roleId",
        params: { role: null },
        views: {
            'menuList': {
                templateUrl: "views/role.html",
                controller: 'RoleCtrl'
            }
        }
    })

    /*
    ** WHITEBOARD
    */

    // whiteboards list view
    .state('app.whiteboards', {
        url: "/projects/:projectId/whiteboards",
        views: {
            'menuList': {
                templateUrl: "views/whiteboards.html",
                controller: 'WhiteboardsCtrl'
            }
        },
        cache: false
    })

    // single whiteboard view
    .state('app.whiteboard', {
          url: "/projects/:projectId/whiteboards/:whiteboardId",
          views: {
            'menuList': {
                templateUrl: "views/whiteboard.html",
                controller: 'WhiteboardCtrl'
            }
        },
        params: {
            whiteboardName: {
                value: 'Whiteboard',
                hiddenParam: 'YES'
            }
        }
    })

    /*
    ** USER
    */

    // profile view
    .state('app.profile', {
        url: "/profile",
        views: {
            'menuList': {
                templateUrl: "views/profile.html",
                controller: 'ProfileCtrl'
            }
        }
    })

    // edit profile view
    .state('app.editProfile', {
        url: "/profile/edit",
        views: {
            'menuList': {
                templateUrl: "views/editProfile.html",
                controller: 'EditProfileCtrl'
            }
        }
    })

    // user view
    .state('app.user', {
        url: ":projectId/user/:userId",
        views: {
            'menuList': {
                templateUrl: "views/user.html",
                controller: 'UserCtrl'
            }
        }
    })

    /*
    ** BUGTRACKER
    */

    // bugtracker view
    .state('app.bugtracker', {
        url: "/projects/:projectId/bugtracker",
        views: {
            'menuList': {
                templateUrl: "views/bugtracker.html",
                controller: 'BugtrackerCtrl'
            }
        },
        cache: false
    })

    // create ticket view
    .state('app.createTicket', {
        url: "/projects/:projectId/bugtracker/createTicket",
        params: { message: null },
        views: {
            'menuList': {
                templateUrl: "views/createTicket.html",
                controller: 'CreateTicketCtrl'
            }
        }
    })

    // ticket view
    .state('app.ticket', {
        url: "/projects/:projectId/bugtracker/ticket/:ticketId",
        views: {
            'menuList': {
                templateUrl: "views/ticket.html",
                controller: 'TicketCtrl'
            }
        }
    })

    // edit ticket view
    .state('app.editTicket', {
        url: "/projects/:projectId/bugtracker/ticket/:ticketId/edit",
        views: {
            'menuList': {
                templateUrl: "views/editTicket.html",
                controller: 'EditTicketCtrl'
            }
        }
    })

    // tag view
    .state('app.tags', {
        url: "/projects/:projectId/bugtracker/tags",
        views: {
            'menuList': {
                templateUrl: "views/tags.html",
                controller: 'TagsCtrl'
            }
        }
    })

    // edit tag view
    .state('app.editTag', {
        url: "/projects/:projectId/bugtracker/tags/:tagId/edit",
        views: {
            'menuList': {
                templateUrl: "views/editTag.html",
                controller: 'EditTagCtrl'
            }
        }
    })

    /*
    ** TIMELINE
    */
    // timeline view
    .state('app.timelines', {
        url: "/projects/:projectId/timelines",
        views: {
            'menuList': {
                templateUrl: "views/timelines.html",
                controller: 'TimelinesCtrl'
            }
        },
        cache: false
    })

    // create message view
    .state('app.createMessage', {
        url: "/projects/:projectId/timelines/:timelineId/createMessage",
        views: {
            'menuList': {
                templateUrl: "views/createMessage.html",
                controller: 'CreateMessageCtrl'
            }
        }
    })

    /*
    ** CLOUD
    */
    // cloud view
    .state('app.cloud', {
        url: "/projects/:projectId/cloud",
        views: {
            'menuList': {
                templateUrl: "views/cloud.html",
                controller: 'CloudCtrl'
            }
        },
        cache: false
    })

    /*
    ** CALENDAR
    */

    // Calendar view
    .state('app.calendar', {
        url: "/projects/?projectId/calendar",
        views: {
            'menuList': {
                templateUrl: "views/calendar.html",
                controller: 'CalendarCtrl'
            }
        },
        cache: false
    })

    // Event creation view
    .state('app.createEvent', {
        url: "/projects/?projectId/calendar/createEvent",
        views: {
            'menuList': {
                templateUrl: "views/createEvent.html",
                controller: 'CreateEventCtrl'
            }
        }
    })

    // Event view
    .state('app.event', {
        url: "/projects/?projectId/calendar/event/:eventId",
        views: {
            'menuList': {
                templateUrl: "views/event.html",
                controller: 'EventCtrl'
            }
        }
    })

    // Event edition view
    .state('app.editEvent', {
        url: "/projects/:projectId/calendar/event/:eventId/edit",
        views: {
            'menuList': {
                templateUrl: "views/editEvent.html",
                controller: 'EditEventCtrl'
            }
        }
    })

    /*
    ** TASKS
    */

    // Tasks view
    .state('app.tasks', {
        url: "/projects/:projectId/tasks",
        views: {
            'menuList': {
                templateUrl: "views/tasks.html",
                controller: 'TasksCtrl'
            }
        }
    })

    // Tasks creation view
    .state('app.createTask', {
        url: "/projects/:projectId/tasks/create",
        views: {
            'menuList': {
                templateUrl: "views/createTask.html",
                controller: 'CreateTaskCtrl'
            }
        }
    })

    // Task view
    .state('app.task', {
        url: "/projects/:projectId/task/:taskId",
        views: {
            'menuList': {
                templateUrl: "views/task.html",
                controller: 'TaskCtrl'
            }
        }
    })

    // Task edition view
    .state('app.editTask', {
        url: "/projects/:projectId/task/:taskId/edit",
        views: {
            'menuList': {
                templateUrl: "views/editTask.html",
                controller: 'EditTaskCtrl'
            }
        }
    })

    /*
    ** GANTT
    */

    // Gantt view
    .state('app.gantt', {
        url: "/projects/:projectId/gantt",
        views: {
            'menuList': {
                templateUrl: "views/gantt.html",
                controller: 'GanttCtrl'
            }
        }
    })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    $urlRouterProvider.otherwise('/auth');
    //$urlRouterProvider.otherwise('/app/whiteboards/1');
});
