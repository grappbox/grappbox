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
    return $resource($rootScope.API + 'projects/addusertoproject');
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

// USERS
.factory('GetProfileInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/basicinformations/:token', { token: "@token" });
})

.factory('GetUserInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getuserbasicinformations/:token/:userId', { token: "@token", userId: "@userId" });
})

.factory('EditProfile', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/basicinformations/:token',
        { token: "@token" },
        { 'update': { method: 'PUT' } }
        );
})

.factory('GetCurrentTasks', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getalltasks/:token', { token: "@token" });
})

// ROLE
.factory('GetProjectRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getprojectroles/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

.factory('GetPersonRolesIdOnProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getrolebyprojectanduser/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
})

.factory('GetUserConnectedRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getuserrolesinformations/:token', { token: "@token" });
})

.factory('GetMemberRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getuserrolesinformations/:token/:id', { token: "@token" });
})

.factory('GetUsersForRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getusersforrole/:token/:roleId', { token: "@token", roleId: "@roleId" });
})

.factory('AddNewRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/addprojectroles');
})

.factory('AssignToRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/assignpersontorole');
})

.factory('UpdateProjectRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/putprojectroles', null, {
        'update': { method: 'PUT' }
    });
})

// GANTT
.factory('GetCurrentAndNextTasks', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getcurrentandnexttasks/:token', { token: "@token" });
})