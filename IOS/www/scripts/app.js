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
    $rootScope.API_VERSION = '0.7'; //actual API's version
    $rootScope.API = 'http://api.grappbox.com/app_dev.php/V' + $rootScope.API_VERSION + '/'; //API full link for controllers
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider) {

    $ionicConfigProvider.views.transition('platform');          // transition between views
    $ionicConfigProvider.backButton.icon('ion-ios-arrow-back'); // iOS back icon
    $ionicConfigProvider.backButton.text('');                   // default is 'Back'
    $ionicConfigProvider.backButton.previousTitleText(false);   // hides the 'Back' text

    $stateProvider
        .state('auth', {
            url: "/auth", //'url' means the rooting of the app as it would be on a web page in URL, we define hand-written
            templateUrl: "templates/auth.html", //'templateUrl' is where app will search for the "physical" page
            controller: 'AuthCtrl' //link to controller
        })

        .state('signup', {
            url: "/signup",
            templateUrl: "templates/signup.html",
            controller: 'SignupCtrl'
        })

        //entering application
        .state('app', {
            url: "/app",
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but pages can inherit of it
            templateUrl: "templates/menu.html"
        })

        .state('app.dashboard', {
            url: "/dashboard/:projectId",
            views: { //here we define the views inheritance
                'menuContent': { //inherites from 'menuContent' in menu.html (<ion-nav-view name="menuContent" [...]</ion-nav-view>)
                    templateUrl: "templates/dashboard.html",
                    controller: 'DashboardCtrl'
                }
            } // because 'app.dashboard' inherits from 'app', urls are concatenated : '/app/dashboard'
        })

        .state('app.projects', {
            url: "/projects",
            views: {
                'menuContent': {
                    templateUrl: "templates/projects.html",
                    controller: 'ProjectsCtrl'
                }
            }
        })

        .state('app.timelines', {
            url: "/timelines",
            views: {
                'menuContent': {
                    templateUrl: "templates/timelines.html",
                    controller: 'TimelinesCtrl'
                }
            }
        })

        .state('app.whiteboards', {
            url: "/whiteboards",
            views: {
                'menuContent': {
                    templateUrl: "templates/whiteboards.html",
                    controller: 'WhiteboardsCtrl'
                }
            }
        })

        .state('app.whiteboard', {
            url: "/whiteboards/:whiteboardId",
            views: {
                'menuContent': {
                    templateUrl: "templates/whiteboard.html",
                    controller: 'WhiteboardCtrl'
                }
            }
        })

        .state('app.userSettings', {
            url: "/userSettings",
            views: {
                'menuContent': {
                    templateUrl: "templates/userSettings.html",
                    controller: 'UserSettingsCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url - It's also the default page when starting application
    $urlRouterProvider.otherwise('/auth');
});