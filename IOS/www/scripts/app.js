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
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but pages can inherit of it
            templateUrl: "views/app.html",
            controller: 'AppCtrl'
        })

        /*
        ** PROJECTS LIST
        */
        // projects list view
        .state('app.projects', {
            url: "/projects",
            views: { //here we define the views inheritance
                'appContent': { //inherites from 'appContent' in app.html (<ion-nav-view name="appContent" [...]</ion-nav-view>)
                    templateUrl: "views/projects.html",
                    controller: 'ProjectsListCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithoutProject.html", // Is in "app.html"
                    controller: 'MenuCtrl'
                }
            }
        })


        /*
        ** DASHBOARD
        */
        // dashboard with Team Occupation, Next Meetings and Global Progress
        .state('app.dashboard', {
            url: "/projects/:projectId/dashboard",
            views: {
                'appContent': {
                    templateUrl: "views/dashboard.html",
                    controller: 'DashboardCtrl',
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        /*
        ** PROJECT
        */
        // single project view
        .state('app.project', {
            url: "/projects/:projectId",
            views: {
                'appContent': {
                    templateUrl: "views/project.html",
                    controller: 'ProjectCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // create project view
        .state('app.createProject', {
            url: "/projects/createProject",
            views: {
                'appContent': {
                    templateUrl: "views/createProject.html",
                    controller: 'CreateProjectCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // edit project view
        .state('app.editProject', {
            url: "/projects/:projectId/edit",
            views: {
                'appContent': {
                    templateUrl: "views/editProject.html",
                    controller: 'EditProjectCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/roles.html",
                    controller: 'RolesCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // users on role view
        .state('app.usersOnRole', {
            url: "/projects/:projectId/roles/:roleId",
            params: { role: null },
            views: {
                'appContent': {
                    templateUrl: "views/usersOnRole.html",
                    controller: 'UsersOnRoleCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/nextMeeting.html",
                    controller: 'NextMeetingCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/whiteboards.html",
                    controller: 'WhiteboardsCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // single whiteboard view
        .state('app.whiteboard', {
            url: "/projects/:projectId/whiteboards/:whiteboardId",
            views: {
                'appContent': {
                    templateUrl: "views/whiteboard.html",
                    controller: 'WhiteboardCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/profile.html",
                    controller: 'ProfileCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // edit profile view
        .state('app.editProfile', {
            url: "/profile/edit",
            views: {
                'appContent': {
                    templateUrl: "views/editProfile.html",
                    controller: 'EditProfileCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // user view
        .state('app.user', {
            url: "/user/:userId",
            views: {
                'appContent': {
                    templateUrl: "views/user.html",
                    controller: 'UserCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/bugtracker.html",
                    controller: 'BugtrackerCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // create ticket view
        .state('app.createTicket', {
            url: "/projects/:projectId/bugtracker/createTicket",
            params: { message: null },
            views: {
                'appContent': {
                    templateUrl: "views/createTicket.html",
                    controller: 'CreateTicketCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // ticket view
        .state('app.ticket', {
            url: "/projects/:projectId/bugtracker/ticket/:ticketId",
            views: {
                'appContent': {
                    templateUrl: "views/ticket.html",
                    controller: 'TicketCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // edit ticket view
        .state('app.editTicket', {
            url: "/projects/:projectId/bugtracker/ticket/:ticketId/edit",
            views: {
                'appContent': {
                    templateUrl: "views/editTicket.html",
                    controller: 'EditTicketCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // tag view
        .state('app.tags', {
            url: "/projects/:projectId/bugtracker/tags",
            views: {
                'appContent': {
                    templateUrl: "views/tags.html",
                    controller: 'TagsCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // edit tag view
        .state('app.editTag', {
            url: "/projects/:projectId/bugtracker/tags/:tagId/edit",
            views: {
                'appContent': {
                    templateUrl: "views/editTag.html",
                    controller: 'EditTagCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/gantt.html",
                    controller: 'GanttCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/timelines.html",
                    controller: 'TimelinesCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

        // create message view
        .state('app.createMessage', {
            url: "/projects/:projectId/timelines/:timelineId/createMessage",
            views: {
                'appContent': {
                    templateUrl: "views/createMessage.html",
                    controller: 'CreateMessageCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
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
                'appContent': {
                    templateUrl: "views/cloud.html",
                    controller: 'CloudCtrl'
                },
                'menuList': {
                    templateUrl: "views/menuWithProject.html",
                    controller: 'MenuCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    $urlRouterProvider.otherwise('/auth');
    //$urlRouterProvider.otherwise('/app/whiteboards/1');
});