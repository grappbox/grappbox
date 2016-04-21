/*
    Summary: API RESTful page using ngResource and factories
*/

angular.module('GrappBox.api', ['ngResource'])

/*
********************* AUTHENTIFICATION *********************
*/
.factory('Auth', function ($rootScope, $resource) {
    return {
        // Login
        Login: function () {
            return $resource($rootScope.API + 'accountadministration/login');
        },
        // Logout
        Logout: function () {
            return $resource($rootScope.API + 'accountadministration/logout/:token', { token: "@token" });
        }
    }
})

/*
********************* DASHBOARD *********************
*/
.factory('Dashboard', function ($rootScope, $resource) {
    return {
        // Get Team Occupation
        TeamOccupation: function () {
            return $resource($rootScope.API + 'dashboard/getteamoccupation/:token', { token: "@token" });
        },
        // Get Next Meetings
        NextMeetings: function () {
            return $resource($rootScope.API + 'dashboard/getnextmeetings/:token', { token: "@token" });
        },
        // Get Projects Global Progress
        GlobalProgress: function () {
            return $resource($rootScope.API + 'dashboard/getprojectsglobalprogress/:token', { token: "@token" });
        }
    }
})


/*
********************* PROJECTS *********************
*/
.factory('Projects', function ($rootScope, $resource) {
    return {
        // Get Projects list
        List: function () {
            return $resource($rootScope.API + 'dashboard/getprojectlist/:token', { token: "@token" });
        },
        // Get Project Information
        Info: function () {
            return $resource($rootScope.API + 'projects/getinformations/:token/:projectId', { token: "@token", projectId: "projectId" });
        },
        // Create Project
        Create: function () {
            return $resource($rootScope.API + 'projects/projectcreation/:token', { token: "@token" });
        },
        // Edit Project
        Edit: function () {
            return $resource($rootScope.API + 'projects/updateinformations', null, {
                'update': { method: 'PUT' }
            });
        },
        // Delete project after 7 days
        Delete: function () {
            return $resource($rootScope.API + 'projects/delproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Retreive Project
        Retreive: function () {
            return $resource($rootScope.API + 'projects/retrieveproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Add user to project
        AddUser: function () {
            return $resource($rootScope.API + 'projects/addusertoproject');
        },
        // Remove user from project
        RemoveUser: function () {
            return $resource($rootScope.API + 'projects/removeusertoproject/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
        },
        // Get Users on project
        Users: function () {
            return $resource($rootScope.API + 'projects/getusertoproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Get all customer accesses on project
        CustomersAccesses: function () {
            return $resource($rootScope.API + 'projects/getcustomeraccessbyproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Generate a customer access on project
        GenCustomerAccess: function () {
            return $resource($rootScope.API + 'projects/getcustomeraccessbyproject/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Delete a customer access
        DeleteCustomerAccess: function () {
            return $resource($rootScope.API + 'projects/delcustomeraccess/:token/:projectId/:customerAccessId', { token: "@token", projectId: "@projectId", customerAccessId: "@customerAccessId" });
        }
    }
})

/*
********************* USERS *********************
*/
.factory('Users', function ($rootScope, $resource) {
    return {
        // Get Profile information
        ProfileInfo: function () {
            return $resource($rootScope.API + 'user/basicinformations/:token', { token: "@token" });
        },
        // Get user information
        UserInfo: function () {
            return $resource($rootScope.API + 'user/getuserbasicinformations/:token/:userId', { token: "@token", userId: "@userId" });
        },
        // Edit profile
        EditProfile: function () {
            return $resource($rootScope.API + 'user/basicinformations/:token', { token: $rootScope.userDatas.token }, {
                'update': { method: 'PUT' }
            });
        },
        // Get current tasks
        CurrentTasks: function () {
            return $resource($rootScope.API + 'user/getalltasks/:token', { token: "@token" });
        }
    }
})


/*
********************* ROLES *********************
*/
.factory('Roles', function ($rootScope, $resource) {
    return {
        // Get all roles on project
        List: function () {
            return $resource($rootScope.API + 'roles/getprojectroles/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Get all roles ID for a user on project
        UserRolesId: function () {
            return $resource($rootScope.API + 'roles/getrolebyprojectanduser/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
        },
        // Get all roles for connected user
        UserConnectedRoles: function () {
            return $resource($rootScope.API + 'roles/getuserrolesinformations/:token', { token: "@token" });
        },
        // Get member roles
        MemberRoles: function () {
            return $resource($rootScope.API + 'roles/getuserrolesinformations/:token/:id', { token: "@token" });
        },
        // Get all users assigned and not assigned on a specific role
        UsersAssignedOrNot: function () {
            return $resource($rootScope.API + 'roles/getusersforrole/:token/:roleId', { token: "@token", roleId: "@roleId" });
        },
        // Add a new role
        Add: function () {
            return $resource($rootScope.API + 'roles/addprojectroles');
        },
        // Delete a role on project
        Delete: function () {
            return $resource($rootScope.API + 'roles/delprojectroles/:token/:id', { token: "@token", id: "@id" });
        },
        // Remove user from role
        RemoveUser: function () {
            return $resource($rootScope.API + 'roles/delpersonrole/:token/:projectId/:userId/:roleId', { token: "@token", projectId: "@projectId", userId: "@userId", roleId: "@roleId" });
        },
        // Assign a member to a role
        Assign: function () {
            return $resource($rootScope.API + 'roles/assignpersontorole');
        },
        // Update project role
        Update: function () {
            return $resource($rootScope.API + 'roles/putprojectroles', null, {
                'update': { method: 'PUT' }
            });
        }
    }
})

/*
********************* BUGTRACKER *********************
*/
.factory('Bugtracker', function ($rootScope, $resource) {
    return {
        // Create a new ticket
        CreateTicket: function () {
            return $resource($rootScope.API + 'bugtracker/postticket/:id', { id: "@id" });
        },
        // Close ticket or comment
        CloseTicketOrComment: function () {
            return $resource($rootScope.API + 'bugtracker/closeticket/:token/:id', { id: "@id", token: "@token" });
        },
        // Reopen ticket
        ReopenTicket: function () {
            return $resource($rootScope.API + 'bugtracker/reopenticket/:token/:id', { id: "@id", token: "@token" }, {
                'update': { method: 'PUT' }
            });
        },
        // Edit a ticket
        EditTicket: function () {
            return $resource($rootScope.API + 'bugtracker/editticket', null, {
                'update': { method: 'PUT' }
            });
        },
        // Edit comment on ticket
        EditCommentOnTicket: function () {
            return $resource($rootScope.API + 'bugtracker/editcomment', null, {
                'update': { method: 'PUT' }
            });
        },
        // (Un)Assign users to ticket
        SetUsersToTicket: function () {
            return $resource($rootScope.API + 'bugtracker/setparticipants', null, {
                'update': { method: 'PUT' }
            });
        },
        // Get all tickets status
        GetTicketsStatus: function () {
            return $resource($rootScope.API + 'bugtracker/getstates/:token', { token: "@token" });
        },
        // Get all open tickets information
        GetOpenTickets: function () {
            return $resource($rootScope.API + 'bugtracker/gettickets/:token/:id', { id: "@id", token: "@token" });
        },
        // Get specific ticket information
        GetTicketInfo: function () {
            return $resource($rootScope.API + 'bugtracker/getticket/:token/:id', { id: "@id", token: "@token" });
        },
        // Get last open tickets information
        GetLastOpenTickets: function () {
            return $resource($rootScope.API + 'bugtracker/getlasttickets/:token/:id/:offset/:limit', { id: "@id", token: "@token", offset: "@offset", limit: "@limit" });
        },
        // Get last closed tickets information
        GetLastClosedTickets: function () {
            return $resource($rootScope.API + 'bugtracker/getlastclosedtickets/:token/:id/:offset/:limit', { id: "@id", token: "@token", offset: "@offset", limit: "@limit" });
        },
        // Get all tickets assigned to a user
        GetTicketsAssignedToUser: function () {
            return $resource($rootScope.API + 'bugtracker/getticketsbyuser/:token/:id/:user', { id: "@id", user: "@user", token: "@token" });
        },
        // Create a new tag
        CreateTag: function () {
            return $resource($rootScope.API + 'bugtracker/tagcreation');
        },
        // Get tag information
        GetTagInfo: function () {
            return $resource($rootScope.API + 'bugtracker/taginformations/:token/:tagId', { token: "@token", tagId: "@tagId" });
        },
        // Get all tags on project
        GetTagsOnProject: function () {
            return $resource($rootScope.API + 'bugtracker/getprojecttags/:token/:projectId', { token: "@token", projectId: "@projectId" });
        },
        // Assign a tag to a ticket
        AssignTag: function () {
            return $resource($rootScope.API + 'bugtracker/assigntag', null, {
                'update': { method: 'PUT' }
            });
        },
        // Post a new comment
        PostComment: function () {
            return $resource($rootScope.API + 'bugtracker/postcomment/:id', { id: "@id" });
        },
        // Get all comments on ticket
        GetCommentsOnTicket: function () {
            return $resource($rootScope.API + 'bugtracker/getcomments/:token/:id/:ticketId', { id: "@id", token: "@token", ticketId: "@ticketId" });
        },
        // Update tag name
        UpdateTag: function () {
            return $resource($rootScope.API + 'bugtracker/tagupdate', null, {
                'update': { method: 'PUT' }
            });
        },
        // Remove tag from ticket
        RemoveTagFromTicket: function () {
            return $resource($rootScope.API + 'bugtracker/removetag/:token/:bugId/:tagId', { token: "@token", bugId: "@bugId", tagId: "@tagId" });
        },
        // Delete tag from project
        DeleteTagFromProject: function () {
            return $resource($rootScope.API + 'bugtracker/deletetag/:token/:tagId', { token: "@token", tagId: "@tagId" });
        }
    }
})

/*
********************* TIMELINE *********************
*/
.factory('Timeline', function ($rootScope, $resource) {
    return {
        // List timelines of a project
        List: function () {
            return $resource($rootScope.API + 'timeline/gettimelines/:token/:id', { token: "@token", id: "@id" });
        },
        // List all messages from a timeline
        Messages: function () {
            return $resource($rootScope.API + 'timeline/getmessages/:token/:id', { token: "@token", id: "@id" });
        },
        // List X last messages from Y offset from a timeline
        LastMessages: function () {
            return $resource($rootScope.API + 'timeline/getlastmessages/:token/:id/:offset/:limit', { token: "@token", id: "@id", offset: "@offset", limit: "@limit" });
        },
        // Post message on a timeline
        PostMessage: function () {
            return $resource($rootScope.API + 'timeline/postmessage/:id', { id: "@data.id" });
        },
        // Edit message or comment on a timeline
        EditMessageOrComment: function () {
            return $resource($rootScope.API + 'timeline/editmessage/:id', { id: "@data.id" }, {
                'update': { method: 'PUT' } });
        },
        // Archive message or comment on a timeline
        ArchiveMessageOrComment: function () {
            return $resource($rootScope.API + 'timeline/archivemessage/:token/:id/:messageId', { token: "@token", id: "@id", messageId: "@messageId" });
        },
        // Get comments on a message
        Comments: function () {
            return $resource($rootScope.API + 'timeline/getcomments/:token/:id/:message', { token: "@token", id: "@id", message: "@message" });
        }
    }
})

/*
********************* CLOUD *********************
** /!\ Download file and Download secure file are handled by $cordovaFileTransfer, see in cloudController.js
*/

.factory('Cloud', function ($rootScope, $resource) {
    return {
        // List files and folders with a path
        List: function () {
            return $resource($rootScope.API + 'cloud/list/:token/:idProject/:path/:passwordSafe', { token: "@token", idProject: "@idProject", path: "@path" });
        },
        // Open stream for a file before uploading
        OpenStream: function () {
            return $resource($rootScope.API + 'cloud/stream/:token/:project_id/:safe_password', { token: "@token", project_id: "@project_id", safe_password: "@safe_password" });
        },
        // Upload the file divided in chunks
        UploadChunks: function () {
            return $resource($rootScope.API + 'cloud/file', null, {
                'update': { method: 'PUT' }
            });
        },
        // Close stream to complete file upload
        CloseStream: function () {
            return $resource($rootScope.API + 'cloud/stream/:token/:project_id/:stream_id', { token: "@token", project_id: "@project_id", stream_id: "@stream_id" });
        },
        // Create a directory
        CreateDir: function () {
            return $resource($rootScope.API + 'cloud/createdir');
        },
        // Set the safe password
        SetSafe: function () {
            return $resource($rootScope.API + 'cloud/safepass', null, {
                'update': { method: 'PUT' }
            });
        },
        // Delete a file or a directory
        DelFileOrDir: function () {
            return $resource($rootScope.API + 'cloud/file/:token/:project_id/:path/:passwordSafe', { token: "@token", project_id: "@project_id", path: "@path" });
        },
        // Delete a secured file
        DelSecuredFile: function () {
            return $resource($rootScope.API + 'cloud/filesecured/:token/:project_id/:path/:password/:passwordSafe', { token: "@token", project_id: "@project_id", path: "@path", password: "@password" });
        }
    }
})

/*
********************* GANTT *********************
*/
// Get current and next tasks
.factory('Gantt', function ($rootScope, $resource) {
    return {
        CurrentAndNextTasks: function () {
            return $resource($rootScope.API + 'user/getcurrentandnexttasks/:token', { token: "@token" });
        }
    }
})