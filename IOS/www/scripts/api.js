/*
    Summary: API RESTful page using ngResource and factories
*/

angular.module('GrappBox.api', ['ngResource'])

// AUTHENTIFICATION
.factory('Login', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/login/:token', {token: "@token"});
})

.factory('Logout', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/logout/:token', {token: "@token"});
})

// DASHBOARD
.factory('Projects', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectlist/:token', { token: "@token" });
})

.factory('TeamOccupation', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getteamoccupation/:token', { token: "@token" });
})

.factory('NextMeetings', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getnextmeetings/:token', { token: "@token" });
})

.factory('GlobalProgress', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectsglobalprogress/:token', { token: "@token" });
})