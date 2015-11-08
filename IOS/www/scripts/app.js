/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'GrappBox.controllers'])

.run(function ($ionicPlatform) {
    $ionicPlatform.ready(function () {
        if (window.StatusBar) {
            StatusBar.styleDefault();
        }
    });
})

.config(function ($ionicConfigProvider, $stateProvider, $urlRouterProvider) {

    $ionicConfigProvider.views.transition('platform');          // transition between views
    $ionicConfigProvider.backButton.icon('ion-ios-arrow-back'); // iOS back icon
    $ionicConfigProvider.backButton.text('');                   // default is 'Back'
    $ionicConfigProvider.backButton.previousTitleText(false);   // hides the 'Back' text

    $stateProvider
        .state('app', {
            url: "/app", //'url' means the rooting of the app as it would be on a web page in URL, we define hand-written
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but every page will inherit of it
            templateUrl: "templates/menu.html" //'templateUrl' is where app will search for the "physical" page
        })

        .state('app.dashboard', {
            url: "/dashboard",
            views: { //here we define the views inheritance
                'menuContent': { //inherites from 'menuContent' in menu.html (<ion-nav-view name="menuContent" [...]</ion-nav-view>)
                    templateUrl: "templates/dashboard.html",
                    controller: 'DashboardCtrl' //link to controller
                }
            } // because 'app.dashboard' inherits from 'app', urls are concatenated : '/app/dashboard'
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
    $urlRouterProvider.otherwise('/app/dashboard');
});