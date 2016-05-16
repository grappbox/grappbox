/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'ngCordova', 'naif.base64', 'GrappBox.controllers', 'GrappBox.api', 'GrappBox.directives'])

// on starting
.run(function ($ionicPlatform, $rootScope, $ionicLoading) {
    $ionicPlatform.ready(function () {
        // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
        // for form inputs)
        /*if (window.cordova && window.cordova.plugins.Keyboard) {
            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
        }*/
        if (window.StatusBar) {
            StatusBar.styleDefault();
        }
    });
    $rootScope.API_VERSION = '0.2'; //actual API's version
    $rootScope.API = 'http://api.grappbox.com/app_dev.php/V' + $rootScope.API_VERSION + '/'; //API full link for controllers

    $rootScope.showLoading = function () {
        $ionicLoading.show({
            template: '<p>Loading...</p><ion-spinner></ion-spinner>'
        });
    };

    $rootScope.hideLoading = function () {
        $ionicLoading.hide();
    };
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider, $httpProvider) {

    //$ionicConfigProvider.views.maxCache(0);
    $ionicConfigProvider.views.swipeBackEnabled(false);
    $ionicConfigProvider.views.transition('platform');          // transition between views
    $ionicConfigProvider.backButton.icon('ion-ios-arrow-back'); // iOS back icon
    $ionicConfigProvider.backButton.text('');                   // default is 'Back'
    $ionicConfigProvider.backButton.previousTitleText(false);   // hides the 'Back' text
    $httpProvider.defaults.headers.delete = { "Content-Type": "application/json;charset=utf-8" };

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
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but pages can inherit of it
            templateUrl: "views/menu.html",
            controller: 'MenuCtrl'
        })

        /*
        ** DASHBOARD
        */
        // dashboard with Team Occupation, Next Meetings and Global Progress
        .state('app.dashboard', {
            url: "/dashboard",
            views: { //here we define the views inheritance
                'menuContent': { //inherites from 'menuContent' in menu.html (<ion-nav-view name="menuContent" [...]</ion-nav-view>)
                    templateUrl: "views/dashboard.html",
                    controller: 'DashboardCtrl',
                }
            } // because 'app.dashboard' inherits from 'app', urls are concatenated : '/app/dashboard'
        })

        /*
        ** PROJECTS
        */
        // projects list view
        .state('app.projects', {
            url: "/projects",
            views: {
                'menuContent': {
                    templateUrl: "views/projects.html",
                    controller: 'ProjectsListCtrl'
                }
            }
        })

        // single project view
        .state('app.project', {
            url: "/projects/:projectId",
            views: {
                'menuContent': {
                    templateUrl: "views/project.html",
                    controller: 'ProjectCtrl'
                }
            }
        })

        // create project view
        .state('app.createProject', {
            url: "/projects/createProject",
            views: {
                'menuContent': {
                    templateUrl: "views/createProject.html",
                    controller: 'CreateProjectCtrl'
                }
            }
        })

        // edit project view
        .state('app.editProject', {
            url: "/projects/:projectId/edit",
            views: {
                'menuContent': {
                    templateUrl: "views/editProject.html",
                    controller: 'EditProjectCtrl'
                }
            }
        })

        /*
        ** PROJECTS ROLES
        */
        // roles view
        .state('app.roles', {
            url: "/projects/:projectId/roles",
            views: {
                'menuContent': {
                    templateUrl: "views/roles.html",
                    controller: 'RolesCtrl'
                }
            }
        })

        // users on role view
        .state('app.usersOnRole', {
            url: "/projects/:projectId/roles/:roleId",
            params: { role: null },
            views: {
                'menuContent': {
                    templateUrl: "views/usersOnRole.html",
                    controller: 'UsersOnRoleCtrl'
                }
            }
        })

        /*
        ** NEXT MEETING
        */
        // next meeting view
        .state('app.nextMeeting', {
            url: "/projects/:nextMeetingId",
            views: {
                'menuContent': {
                    templateUrl: "views/nextMeeting.html",
                    controller: 'NextMeetingCtrl'
                }
            }
        })

        /*
        ** WHITEBOARD
        */

        // whiteboards list view
        .state('app.whiteboards', {
            url: "/whiteboards/:projectId",
            views: {
                'menuContent': {
                    templateUrl: "views/whiteboards.html",
                    controller: 'WhiteboardsCtrl'
                }
            }
        })

        // single whiteboard view
        .state('app.whiteboard', {
            url: "whiteboards/:projectId/:whiteboardId",
            views: {
                'menuContent': {
                    templateUrl: "views/whiteboard.html",
                    controller: 'WhiteboardCtrl'
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
                'menuContent': {
                    templateUrl: "views/profile.html",
                    controller: 'ProfileCtrl'
                }
            }
        })

        // edit profile view
        .state('app.editProfile', {
            url: "/profile/edit",
            views: {
                'menuContent': {
                    templateUrl: "views/editProfile.html",
                    controller: 'EditProfileCtrl'
                }
            }
        })

        // user view
        .state('app.user', {
            url: "/user/:userId",
            views: {
                'menuContent': {
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
                'menuContent': {
                    templateUrl: "views/bugtracker.html",
                    controller: 'BugtrackerCtrl'
                }
            }
        })

        // create ticket view
        .state('app.createTicket', {
            url: "/projects/:projectId/bugtracker/createTicket",
            params: { message: null },
            views: {
                'menuContent': {
                    templateUrl: "views/createTicket.html",
                    controller: 'CreateTicketCtrl'
                }
            }
        })

        // ticket view
        .state('app.ticket', {
            url: "/projects/:projectId/bugtracker/ticket/:ticketId",
            views: {
                'menuContent': {
                    templateUrl: "views/ticket.html",
                    controller: 'TicketCtrl'
                }
            }
        })

        // edit ticket view
        .state('app.editTicket', {
            url: "/projects/:projectId/bugtracker/ticket/:ticketId/edit",
            views: {
                'menuContent': {
                    templateUrl: "views/editTicket.html",
                    controller: 'EditTicketCtrl'
                }
            }
        })

        // tag view
        .state('app.tags', {
            url: "/projects/:projectId/bugtracker/tags",
            views: {
                'menuContent': {
                    templateUrl: "views/tags.html",
                    controller: 'TagsCtrl'
                }
            }
        })

        // edit tag view
        .state('app.editTag', {
            url: "/projects/:projectId/bugtracker/tags/:tagId/edit",
            views: {
                'menuContent': {
                    templateUrl: "views/editTag.html",
                    controller: 'EditTagCtrl'
                }
            }
        })

        /*
        ** GANTT
        */

        // Gantt view
        .state('app.gantt', {
            url: "/projects/:projectId/gantt/",
            views: {
                'menuContent': {
                    templateUrl: "views/gantt.html",
                    controller: 'GanttCtrl'
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
                'menuContent': {
                    templateUrl: "views/timelines.html",
                    controller: 'TimelinesCtrl'
                }
            }
        })

        // create message view
        .state('app.createMessage', {
            url: "/projects/:projectId/timelines/:timelineId/createMessage",
            views: {
                'menuContent': {
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
                'menuContent': {
                    templateUrl: "views/cloud.html",
                    controller: 'CloudCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    $urlRouterProvider.otherwise('/auth');
    //$urlRouterProvider.otherwise('/app/whiteboards/1');
});