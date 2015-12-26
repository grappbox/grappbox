/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'GrappBox.controllers', 'GrappBox.api'])

// on starting
.run(function ($ionicPlatform, $rootScope) {
    $ionicPlatform.ready(function () {
        if (window.StatusBar) {
            StatusBar.styleDefault();
        }
    });
    $rootScope.API_VERSION = '0.8'; //actual API's version
    $rootScope.API = 'http://api.grappbox.com/app_dev.php/V' + $rootScope.API_VERSION + '/'; //API full link for controllers
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider) {

    $ionicConfigProvider.views.transition('platform');          // transition between views
    $ionicConfigProvider.backButton.icon('ion-ios-arrow-back'); // iOS back icon
    $ionicConfigProvider.backButton.text('');                   // default is 'Back'
    $ionicConfigProvider.backButton.previousTitleText(false);   // hides the 'Back' text

    $stateProvider
        // authentification view
        .state('auth', {
            url: "/auth", //'url' means the rooting of the app as it would be on a web page in URL, we define hand-written
            templateUrl: "views/auth.html", //'templateUrl' is where app will search for the "physical" page
            controller: 'AuthCtrl' //link to controller
        })

        // signup view
        .state('signup', {
            url: "/signup",
            templateUrl: "views/signup.html",
            controller: 'SignupCtrl'
        })

        // entering application
        .state('app', {
            url: "/app",
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but pages can inherit of it
            templateUrl: "views/menu.html",
            controller: 'MenuCtrl'
        })

        // dashboard with Team Occupation, Next Meetings and Global Progress
        .state('app.dashboard', {
            url: "/dashboard",
            views: { //here we define the views inheritance
                'menuContent': { //inherites from 'menuContent' in menu.html (<ion-nav-view name="menuContent" [...]</ion-nav-view>)
                    templateUrl: "views/dashboard.html",
                    controller: 'DashboardCtrl'
                }
            } // because 'app.dashboard' inherits from 'app', urls are concatenated : '/app/dashboard'
        })

        // projects list view
        .state('app.projects', {
            url: "/projects",
            views: {
                'menuContent': {
                    templateUrl: "views/projects.html",
                    controller: 'ProjectsCtrl'
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

        // timeline view
        .state('app.timelines', {
            url: "/timelines",
            views: {
                'menuContent': {
                    templateUrl: "views/timelines.html",
                    controller: 'TimelinesCtrl'
                }
            }
        })

        // whiteboards list view
        .state('app.whiteboards', {
            url: "/whiteboards",
            views: {
                'menuContent': {
                    templateUrl: "views/whiteboards.html",
                    controller: 'WhiteboardsCtrl'
                }
            }
        })

        // single whiteboard view
        .state('app.whiteboard', {
            url: "/whiteboards/:whiteboardId",
            views: {
                'menuContent': {
                    templateUrl: "views/whiteboard.html",
                    controller: 'WhiteboardCtrl'
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

        // user settings view
        .state('app.userSettings', {
            url: "/userSettings",
            views: {
                'menuContent': {
                    templateUrl: "views/userSettings.html",
                    controller: 'UserSettingsCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    //$urlRouterProvider.otherwise('/auth');
    $urlRouterProvider.otherwise('/app/whiteboards/1');
});