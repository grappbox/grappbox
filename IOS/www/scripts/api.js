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

// Delete project after 7 days
.factory('DeleteProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/delproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Retreive Project
.factory('RetreiveProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/retrieveproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
})

// Add user to project
.factory('AddUserToProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/addusertoproject');
})

// Remove user from project
.factory('RemoveUserFromProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/removeusertoproject/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
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

// Delete a customer access
.factory('DeleteCustomerAccess', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'projects/delcustomeraccess/:token/:projectId/:customerAccessId', { token: "@token", projectId: "@projectId", customerAccessId: "@customerAccessId" });
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

// Delete a role on project
.factory('DeleteProjectRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/delprojectroles/:token/:id', { token: "@token", id: "@id"});
})

// Remove user from role
.factory('RemoveUserFromRole', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'roles/delpersonrole/:token/:projectId/:userId/:roleId', { token: "@token", projectId: "@projectId", userId: "@userId", roleId: "@roleId" });
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

// Close ticket or comment
.factory('CloseTicketOrComment', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/closeticket/:token/:id', { id: "@id", token: "@token" });
})

// Reopen ticket
.factory('ReopenTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/reopenticket/:token/:id', { id: "@id", token: "@token" },
    { 'update': { method: 'PUT' } });
})

// Edit a ticket
.factory('EditTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/editticket', null, {
        'update': { method: 'PUT' }
    });
})

// Edit comment on ticket
.factory('EditCommentOnTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/editcomment', null, {
        'update': { method: 'PUT' }
    });
})

// (Un)Assign users to ticket
.factory('SetUsersToTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/setparticipants', null, 
        { 'update': { method: 'PUT' } });
})

// Get all tickets status
.factory('GetTicketsStatus', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getstates/:token', { token: "@token" });
})

// Get all open tickets information
.factory('GetOpenTickets', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/gettickets/:token/:id', { id: "@id", token: "@token" });
})

// Get specific ticket information
.factory('GetTicketInfo', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getticket/:token/:id', { id: "@id", token: "@token" });
})

// Get last tickets information
.factory('GetLastOpenTickets', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getlasttickets/:token/:id/:offset/:limit', { id: "@id", token: "@token", offset: "@offset", limit: "@limit" });
})

// Get last closed ticket information
.factory('GetLastClosedTickets', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/getlastclosedtickets/:token/:id/:offset/:limit', { id: "@id", token: "@token", offset: "@offset", limit: "@limit" });
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

// Assign a tag to a ticket
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

// Remove tag from ticket
.factory('RemoveTagFromTicket', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/removetag/:token/:bugId/:tagId', { token: "@token", bugId: "@bugId", tagId: "@tagId" });
})

// Delete tag from project
.factory('DeleteTagFromProject', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'bugtracker/deletetag/:token/:tagId', { token: "@token", tagId: "@tagId" });
})

/*
********************* TIMELINE *********************
*/
// List timelines of a project
.factory('GetTimelines', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/gettimelines/:token/:id', { token: "@token", id: "@id" });
})

// List all messages from a timeline
.factory('GetMessages', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/getmessages/:token/:id', { token: "@token", id: "@id" });
})

// List X last messages from Y offset from a timeline
.factory('GetLastMessages', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/getlastmessages/:token/:id/:offset/:limit', { token: "@token", id: "@id", offset: "@offset", limit: "@limit" });
})

// Post message on a timeline
.factory('PostMessage', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/postmessage/:id', { id: "@data.id" });
})

// Edit message or comment on a timeline
.factory('EditMessageOrComment', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/editmessage/:id', { id: "@data.id" },
    { 'update': { method: 'PUT' } });
})

// Archive message or comment on a timeline
.factory('ArchiveMessageOrComment', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/archivemessage/:token/:id/:messageId', { token: "@token", id: "@id", messageId: "@messageId" });
})

.factory('GetCommentsOnTimeline', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'timeline/getcomments/:token/:id/:message', { token: "@token", id: "@id", message: "@message" });
})

/*
********************* CLOUD *********************
*/

// Download file and Download secure file are handled by $cordovaFileTransfer

// List files and folders with a path
.factory('CloudLS', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/list/:token/:idProject/:path/:passwordSafe', { token: "@token", idProject: "@idProject", path: "@path" });
})

// Open stream for a file before uploading
.factory('CloudOpenStream', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/stream/:token/:project_id', { token: "@token", project_id: "@project_id" });
})

// Upload the file divided in chunks
.factory('CloudUploadChunk', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/file', null, 
        { 'update': { method: 'PUT' } });
})

// Close stream to complete file upload
.factory('CloudCloseStream', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/stream/:token/:project_id/:stream_id', { token: "@token", project_id: "@project_id", stream_id: "@stream_id"});
})

// Create a directory
.factory('CloudCreateDir', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/createdir');
})

// Set the safe password
.factory('CloudSetSafe', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/safepass', null,
        { 'update': { method: 'PUT' } });
})

// Delete a file or a directory
.factory('CloudDelFileOrDir', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/file/:token/:project_id/:path/:password', { token: "@token", project_id: "@project_id", path: "@path", password: "@password" });
})

// Delete a secured file or a secured directory
.factory('CloudDelSecuredFileOrDir', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'cloud/filesecured/:token/:project_id/:path/:password/:safe_password', { token: "@token", project_id: "@project_id", path: "@path", password: "@password", safe_password: "@safe_password" });
})

/*
********************* GANTT *********************
*/
// Get current and next tasks
.factory('GetCurrentAndNextTasks', function ($rootScope, $resource) {
    return $resource($rootScope.API + 'user/getcurrentandnexttasks/:token', { token: "@token" });
});