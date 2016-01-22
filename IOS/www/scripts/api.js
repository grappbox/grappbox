/*
    Summary: API RESTful page using ngResource and factories
*/

angular.module('GrappBox.api', ['ngResource'])

/*
********************* AUTHENTIFICATION *********************
*/
// Login
.factory('Login', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/login');
})
// Logout
.factory('Logout', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'accountadministration/logout/:token', { token: "@token" });
})


/*
********************* DASHBOARD *********************
*/
// Get Team Occupation
.factory('TeamOccupation', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getteamoccupation/:token', { token: "@token" });
})

// Get Next Meetings
.factory('NextMeetings', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getnextmeetings/:token', { token: "@token" });
})

// Get Projects Global Progress
.factory('GlobalProgress', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectsglobalprogress/:token', { token: "@token" });
})


/*
********************* PROJECTS *********************
*/
// Get Projects list
.factory('ProjectsList', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'dashboard/getprojectlist/:token', { token: "@token" });
})

// Get Project Information
.factory('ProjectView', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getinformations/:token/:projectId', { token: "@token", projectId: "projectId" });
})

// Create Project
.factory('CreateProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/projectcreation/:token', { token: "@token" });
})

// Edit Project
.factory('EditProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/updateinformations', null, {
        'update': { method: 'PUT' }
    });
})

// Retreive Project
.factory('RetreiveProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/retrieveproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Add user to project
.factory('AddUserToProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/addusertoproject');
})

// Get Users on project
.factory('UsersOnProjectList', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getusertoproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Get all customer accesses on project
.factory('GetCustomersAccessOnProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/getcustomeraccessbyproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Generate a customer access on project
.factory('GenCustomerAccess', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/generatecustomeraccess');
})


/*
********************* USERS *********************
*/
// Get Profile information
.factory('GetProfileInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/basicinformations/:token', { token: "@token" });
})

// Get user information
.factory('GetUserInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getuserbasicinformations/:token/:userId', { token: "@token", userId: "@userId" });
})

// Edit profile
.factory('EditProfile', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/basicinformations/:token', { token: $rootScope.userDatas.token },
        { 'update': { method: 'PUT' } }
        );
})

// Get current tasks
.factory('GetCurrentTasks', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getalltasks/:token', { token: "@token" });
})


/*
********************* ROLE *********************
*/
// Get all roles on project    
.factory('GetProjectRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getprojectroles/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Get all roles for a user on project
.factory('GetPersonRolesIdOnProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getrolebyprojectanduser/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
})

// Get all roles for connected user
.factory('GetUserConnectedRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getuserrolesinformations/:token', { token: "@token" });
})

// Get member roles
.factory('GetMemberRoles', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getuserrolesinformations/:token/:id', { token: "@token" });
})

// Get all users on a specific role
.factory('GetUsersForRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/getusersforrole/:token/:roleId', { token: "@token", roleId: "@roleId" });
})

// Add a new role
.factory('AddNewRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/addprojectroles');
})

// Assign a member to a role
.factory('AssignToRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/assignpersontorole');
})

// Update project role
.factory('UpdateProjectRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/putprojectroles', null, {
        'update': { method: 'PUT' }
    });
})

/*
********************* BUGTRACKER *********************
*/
// Create a new ticket
.factory('CreateTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/postticket/:id', { id: "@id" });
})

// Edit a ticket
.factory('EditTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/editticket/:id', { id: "@id" });
})

// Edit comment on ticket
.factory('EditCommentOnTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/editticket/:id', { id: "@id" });
})

// Assign users to ticket
.factory('SetUsersToTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/setparticipants/:id', { id: "@id" });
})

// Get all tickets status
.factory('GetTicketsStatus', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getstates/:token', { token: "@token" });
})

// Get all tickets information
.factory('GetAllTickets', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/gettickets/:token/:id', { id: "@id", token: "@token" });
})

// Get specific ticket information
.factory('GetTicketInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getticket/:token/:id', { id: "@id", token: "@token" });
})

// Get all tickets assigned to a user
.factory('GetTicketsAssignedToUser', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getticketsbyuser/:token/:id/:user', { id: "@id", user: "@user", token: "@token" });
})

// Create a new tag
.factory('CreateTag', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/tagcreation');
})

// Get tag information
.factory('GetTagInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/taginformations/:token/:tagId', { token: "@token", tagId: "@tagId" });
})

// Get all tags on project
.factory('GetTagsOnProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getprojecttags/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Assign a tag to a member
.factory('AssignTag', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/assigntag', null, {
        'update': { method: 'PUT' }
    });
})

// Post a new comment
.factory('PostComment', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/postcomment/:id', { id: "@id" });
})

// Get all comments on a ticket
.factory('GetCommentsOnTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getcomments/:token/:id/:ticketId', { id: "@id", token: "@token", ticketId: "@ticketId" });
})

// Update tag name
.factory('UpdateTag', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/tagupdate', null, {
        'update': { method: 'PUT' }
    });
})

/*
********************* GANTT *********************
*/
// Get current and next tasks
.factory('GetCurrentAndNextTasks', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getcurrentandnexttasks/:token', { token: "@token" });
});