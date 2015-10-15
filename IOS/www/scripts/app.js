/*
    Summary: app.js stocks the app configuration routing
    Every pages mentionned here are stocked in Templates folder
*/

angular.module('GrappBox', ['ionic', 'GrappBox.controllers'])

.config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider
        .state('app', {
            url: "/app", //'url' means the rooting of the app as it would be on a web page in URL, we define hand-written
            abstract: true, //'abstract' means this state will be an abstract, so will never render, but every page will inherit of it
            templateUrl: "templates/menu.html", //'templateUrl' is where app will search for the "physical" page
            controller: 'AppCtrl' //Link to the controller in controllers.js
        })

        .state('app.dashboard', {
            url: "/dashboard",
            views: { //here we define the views inheritance
                'menuContent': { //inherites from 'menuContent' in menu.html (<ion-nav-view name="menuContent" [...]</ion-nav-view>)
                    templateUrl: "templates/dashboard.html",
                    controller: 'DashboardCtrl'
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

        .state('app.whiteboard', {
            url: "/whiteboard",
            views: {
                'menuContent': {
                    templateUrl: "templates/whiteboard.html",
                    controller: 'WhiteboardCtrl'
                }
            }
        })

        .state('app.settings', {
            url: "/settings",
            views: {
                'menuContent': {
                    templateUrl: "templates/settings.html",
                    controller: 'SettingsCtrl'
                }
            }
        })

    // if no state are found, here is the fallback url
    $urlRouterProvider.otherwise('/app/dashboard');
});