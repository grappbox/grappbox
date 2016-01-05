/*
    Summary: API RESTful page using ngResource and factories
*/

angular.module('GrappBox.api', ['ngResource'])

// AUTHENTIFICATION
.factory('Login', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/login/:token', { token: "@token" });
})

.factory('Logout', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/logout/:token', { token: "@token" });
})

// DASHBOARD

.factory('TeamOccupation', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getteamoccupation/:token', { token: "@token" });
})

.factory('NextMeetings', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getnextmeetings/:token', { token: "@token" });
})

.factory('GlobalProgress', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectsglobalprogress/:token', { token: "@token" });
})

// PROJECTS

.factory('ProjectsList', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectlist/:token', { token: "@token" });
})

.factory('ProjectView', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getinformations/:token/:projectId', { token: "@token", projectId: "projectId" });
})

.factory('CreateProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/projectcreation/:token', { token: "@token" });
})

.factory('EditProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/updateinformations', null, {
        'update': { method: 'PUT' }
    });
})

.factory('RetreiveProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/retrieveproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

.factory('AddUserToProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/addusertoproject', null, {
        'update': { method: 'PUT' }
    });
})

.factory('UsersOnProjectList', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getusertoproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

.factory('GetCustomersAccessOnProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getcustomeraccessbyproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

.factory('GenCustomerAccess', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/generatecustomeraccess');
})