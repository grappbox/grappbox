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
