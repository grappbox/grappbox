/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'ngCordova', 'naif.base64', 'GrappBox.controllers', 'GrappBox.api', 'GrappBox.directives', 'GrappBox.factories'])

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

    $rootScope.hasProject = false;

    $rootScope.showLoading = function () {
        $ionicLoading.show({
            template: '<p>Loading...</p><ion-spinner></ion-spinner>', noBackdrop: true
        });
    };

    $rootScope.hideLoading = function () {
        $ionicLoading.hide();
    };
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider, $httpProvider) {

    //$ionicConfigProvider.views.maxCache(0);
    //$ionicConfigProvider.views.swipeBackEnabled(false);
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
        // roles view
        .state('app.roles', {
            url: "/projects/:projectId/roles",
            views: {
                'menuList': {
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
                'menuList': {
                    templateUrl: "views/usersOnRole.html",
                    controller: 'UsersOnRoleCtrl'
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
            }
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
            url: "/user/:userId",
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
            }
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
            }
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
            }
        })

        /*
        ** CALENDAR
        */

        // Tasks view
        .state('app.calendar', {
            url: "/projects/:projectId/calendar/",
            views: {
                'menuList': {
                    templateUrl: "views/calendar.html",
                    controller: 'CalendarCtrl'
                }
            }
        })

        /*
        ** TASKS
        */

        // Tasks view
        .state('app.tasks', {
            url: "/projects/:projectId/tasks/",
            views: {
                'menuList': {
                    templateUrl: "views/tasks.html",
                    controller: 'TasksCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    $urlRouterProvider.otherwise('/auth');
    //$urlRouterProvider.otherwise('/app/whiteboards/1');
});