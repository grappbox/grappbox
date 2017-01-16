/*
Summary: API RESTful page using ngResource and factories
*/
/*
** ngResource params: { id : "@id" } form -> id = in URL, @id = in 'data' object. If an object in data is wanted in URL, use "@data.objectWanted" for example
*/

angular.module('GrappBox.api', ['ngResource'])

/*
********************* AUTHENTIFICATION *********************
*/
.factory('Auth', function ($rootScope, $resource) {
  return {
    // Login
    Login: function () {
      return $resource($rootScope.API + 'account/login');
    },
    // Logout
    Logout: function () {
      return $resource($rootScope.API + 'account/logout');
    }
  }
})

/*
********************* DASHBOARD *********************
*/
.factory('Dashboard', function ($rootScope, $resource) {
  return {
    // Get Projects list and get their global progress
    List: function () {
      return $resource($rootScope.API + 'dashboard/projects');
    },
    // Get Team Occupation
    TeamOccupation: function () {
      return $resource($rootScope.API + 'dashboard/occupation/:id', { id: "@id" });
    },
    // Get Next Meetings
    NextMeetings: function () {
      return $resource($rootScope.API + 'dashboard/meetings/:id', { id: "@id" });
    },
  }
})


/*
********************* PROJECTS *********************
*/
.factory('Projects', function ($rootScope, $resource) {
  return {
    // Get Project Information
    Info: function () {
      return $resource($rootScope.API + 'project/:id', { id: "id" });
    },
    // Create Project
    Create: function () {
      return $resource($rootScope.API + 'project');
    },
    // Edit Project
    Edit: function () {
      return $resource($rootScope.API + 'projects/updateinformations', null, {
        'update': { method: 'PUT' }//, transformRequest: $rootScope.dropUnchangedFields }
      });
    },
    // Delete project after 7 days
    Delete: function () {
      return $resource($rootScope.API + 'project/:id', { id: "@id" });
    },
    // Retreive Project
    Retreive: function () {
      return $resource($rootScope.API + 'project/retrieve/:projectId', { projectId: "@projectId" });
    },
    // Add user to project
    AddUser: function () {
      return $resource($rootScope.API + 'project/user');
    },
    // Remove user from project
    RemoveUser: function () {
      return $resource($rootScope.API + 'project/user/:projectId/:userId', { projectId: "@projectId", userId: "@userId" });
    },
    // Get Users on project
    Users: function () {
      return $resource($rootScope.API + 'project/users/:id', { id: "@id" });
    },
    // Get all customer accesses on project
    CustomersAccesses: function () {
      return $resource($rootScope.API + 'project/customeraccesses/:projectId', { projectId: "@projectId" });
    },
    // Generate a customer access on project
    GenCustomerAccess: function () {
      return $resource($rootScope.API + 'project/customeraccess');
    },
    // Delete a customer access
    DeleteCustomerAccess: function () {
      return $resource($rootScope.API + 'project/customeraccess/:projectId/:customerAccessId', { projectId: "@projectId", customerAccessId: "@customerAccessId" });
    },
    // Get logo
    Logo: function () {
      return $resource($rootScope.API + 'project/logo/:id', { id: "@id" });
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
      return $resource($rootScope.API + 'user');
    },
    // Get user information
    UserInfo: function () {
      return $resource($rootScope.API + 'user/:userId', { userId: "@userId" });
    },
    // Edit profile
    EditProfile: function () {
      return $resource($rootScope.API + 'user', {}, {
        'update': { method: 'PUT' } //transformRequest: $rootScope.dropUnchangedFields }
      });
    },
    // Get current tasks
    CurrentTasks: function () {
      return $resource($rootScope.API + 'user/getalltasks/:token', { token: "@token" });
    },
    // Get specific user avatar
    Avatar: function () {
      return $resource($rootScope.API + 'user/avatar/:userId', { userId: "@userId" });
    },
    // Get users on a project avatar
    Avatars: function () {
      return $resource($rootScope.API + 'user/project/avatars/:projectId', { projectId: "@projectId" });
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
      return $resource($rootScope.API + 'roles/:projectId', { projectId: "@projectId" });
    },
    // Get all roles ID for a user on project
    UserRolesId: function () {
      return $resource($rootScope.API + 'roles/getrolebyprojectanduser/:token/:projectId/:userId', { token: "@token", projectId: "@projectId", userId: "@userId" });
    },
    // Get all roles for connected user | V0.2 (16/09/2016)
    UserConnectedRoles: function () {
      //return $resource($rootScope.API + 'roles/getuserrolesinformations/:token', { token: "@token" });  | V0.2 (< 16/09/2016)
      return $resource($rootScope.API + 'roles/getuserroles/:token', { token: "@token" });
    },
    // Get member roles
    MemberRoles: function () {
      //return $resource($rootScope.API + 'roles/getuserrolesinformations/:token/:id', { token: "@token" }); | V0.2 (< 16/09/2016)
      return $resource($rootScope.API + 'roles/getrolebyprojectanduser/:token/:projectId/:userId', { token: "@token", projectId: "@projectId" });
    },
    // Get all users assigned and not assigned on a specific role
    UsersAssignedOrNot: function () {
      return $resource($rootScope.API + 'role/users/:roleId', { roleId: "@roleId" });
    },
    // Add a new role
    Add: function () {
      return $resource($rootScope.API + 'role');
    },
    // Delete a role on project
    Delete: function () {
      return $resource($rootScope.API + 'role/:id', { id: "@id" });
    },
    // Remove user from role
    RemoveUser: function () {
      return $resource($rootScope.API + 'role/user/:projectId/:userId/:roleId', { projectId: "@projectId", userId: "@userId", roleId: "@roleId" });
    },
    // Assign a member to a role
    Assign: function () {
      return $resource($rootScope.API + 'role/user');
    },
    // Update project role
    Update: function () {
      return $resource($rootScope.API + 'role/:id', { id: "@roleId" }, {
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
      return $resource($rootScope.API + 'bugtracker/ticket');
    },
    // Close ticket
    CloseTicket: function () {
      return $resource($rootScope.API + 'bugtracker/ticket/close/:id', { id: "@id" });
    },
    // Reopen ticket
    ReopenTicket: function () {
      return $resource($rootScope.API + 'bugtracker/ticket/reopen/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // Edit a ticket
    EditTicket: function () {
      return $resource($rootScope.API + 'bugtracker/ticket/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // Edit comment on ticket
    EditCommentOnTicket: function () {
      return $resource($rootScope.API + 'bugtracker/comment/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // (Un)Assign users to ticket
    SetUsersToTicket: function () {
      return $resource($rootScope.API + 'bugtracker/users/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // Get all tickets status
    /*GetTicketsStatus: function () {
    return $resource($rootScope.API + 'bugtracker/getstates/:token', { token: "@token" });
  },*/
  // Get all open tickets information
  GetOpenTickets: function () {
    return $resource($rootScope.API + 'bugtracker/gettickets/:token/:id', { id: "@id", token: "@token" });
  },
  // Get specific ticket information
  GetTicketInfo: function () {
    return $resource($rootScope.API + 'bugtracker/ticket/:id', { id: "@id" });
  },
  // Get last open tickets information
  GetLastOpenTickets: function () {
    return $resource($rootScope.API + 'bugtracker/tickets/opened/:id/:offset/:limit', { id: "@id", offset: "@offset", limit: "@limit" });
  },
  // Get last closed tickets information
  GetLastClosedTickets: function () {
    return $resource($rootScope.API + 'bugtracker/tickets/closed/:id/:offset/:limit', { id: "@id", offset: "@offset", limit: "@limit" });
  },
  // Get all tickets assigned to a user
  GetTicketsAssignedToUser: function () {
    return $resource($rootScope.API + 'bugtracker/tickets/user/:id/:user', { id: "@id", user: "@user" });
  },
  // Create a new tag
  CreateTag: function () {
    return $resource($rootScope.API + 'bugtracker/tag');
  },
  // Get tag information
  GetTagInfo: function () {
    return $resource($rootScope.API + 'bugtracker/tag/:id', { id: "id" });
  },
  // Get all tags on project
  GetTagsOnProject: function () {
    return $resource($rootScope.API + 'bugtracker/project/tags/:projectId', { projectId: "@projectId" });
  },
  // Assign a tag to a ticket
  AssignTag: function () {
    return $resource($rootScope.API + 'bugtracker/tag/assign/:bugId', { bugId: "@bugId" }, {
      'update': { method: 'PUT' }
    });
  },
  // Post a new comment
  PostComment: function () {
    return $resource($rootScope.API + 'bugtracker/comment');
  },
  // Get all comments on ticket
  GetCommentsOnTicket: function () {
    return $resource($rootScope.API + 'bugtracker/comments/:ticketId', { ticketId: "@ticketId" });
  },
  // Update tag name
  UpdateTag: function () {
    return $resource($rootScope.API + 'bugtracker/tag/:id', { id: "@id" }, {
      'update': { method: 'PUT' }
    });
  },
  // Remove tag from ticket
  RemoveTagFromTicket: function () {
    return $resource($rootScope.API + 'bugtracker/tag/remove/:bugId/:tagId', { bugId: "@bugId", tagId: "@tagId" });
  },
  // Delete tag from project
  DeleteTagFromProject: function () {
    return $resource($rootScope.API + 'bugtracker/tag/:id', { id: "@id" });
  },
  // Delete a comment
  DeleteComment: function () {
    return $resource($rootScope.API + 'bugtracker/comment/:id', { id: "@id" })
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
      return $resource($rootScope.API + 'timelines/:id', { id: "@id" });
    },
    // List all messages from a timeline
    Messages: function () {
      return $resource($rootScope.API + 'timeline/getmessages/:token/:id', { token: "@token", id: "@id" });
    },
    // List X last messages from Y offset from a timeline
    LastMessages: function () {
      return $resource($rootScope.API + 'timeline/messages/:id/:offset/:limit', { id: "@id", offset: "@offset", limit: "@limit" });
    },
    // Post message on a timeline
    PostMessage: function () {
      return $resource($rootScope.API + 'timeline/message/:id', { id: "@id" });
    },
    PostComment: function () {
      return $resource($rootScope.API + 'timeline/comment/:id', { id: "@id" });
    },
    // Edit message or comment on a timeline
    EditMessage: function () {
      return $resource($rootScope.API + 'timeline/message/:id/:messageId', { id: "@id", messageId: "@data.messageId" }, {
        'update': { method: 'PUT' }
      });
    },
    // Edit comment on a timeline
    EditComment: function () {
      return $resource($rootScope.API + 'timeline/comment/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // Archive message or comment on a timeline
    ArchiveMessageAndComments: function () {
      return $resource($rootScope.API + 'timeline/message/:id/:messageId', { id: "@id", messageId: "@messageId" });
    },
    // Delete comment on a message
    DeleteComment: function () {
      return $resource($rootScope.API + 'timeline/comment/:commentId', { commentId: "@commentId" });
    },
    // Get comments on a message
    Comments: function () {
      return $resource($rootScope.API + 'timeline/message/comments/:id/:messageId', { id: "@id", messageId: "@messageId" });
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
      return $resource($rootScope.API + 'cloud/list/:idProject/:path/:passwordSafe', { idProject: "@idProject", path: "@path" });
    },
    // Open stream for a file before uploading
    OpenStream: function () {
      return $resource($rootScope.API + 'cloud/stream/:project_id/:safe_password', { project_id: "@project_id", safe_password: "@safe_password" });
    },
    // Upload the file divided in chunks
    UploadChunks: function () {
      return $resource($rootScope.API + 'cloud/file', null, {
        'update': { method: 'PUT' }
      });
    },
    // Close stream to complete file upload
    CloseStream: function () {
      return $resource($rootScope.API + 'cloud/stream/:project_id/:stream_id', { project_id: "@project_id", stream_id: "@stream_id" });
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
      return $resource($rootScope.API + 'cloud/file/:project_id/:path/:passwordSafe', { project_id: "@project_id", path: "@path" });
    },
    // Delete a secured file
    DelSecuredFile: function () {
      return $resource($rootScope.API + 'cloud/filesecured/:project_id/:path/:password/:passwordSafe', { project_id: "@project_id", path: "@path", password: "@password" });
    },
    Download: function () {
      return $resource($rootScope.API + 'cloud/file/:cloudPath/:idProject/:passwordSafe', { cloudPath: "@cloudPath", idProject: "@idProject" }, {
        get: {
          method: 'GET',
          transformResponse: function (data, headers) {
            response = {}
            response.data = data;
            response.headers = headers();
            return response;
          }
        }
      })
    },
    DownloadSecure: function () {
      return $resource($rootScope.API + 'cloud/filesecured/:cloudPath/:idProject/:password/:passwordSafe', { cloudPath: "@cloudPath", idProject: "@idProject" }, {
        get: {
          method: 'GET',
          transformResponse: function (data, headers) {
            response = {}
            response.data = data;
            response.headers = headers();
            return response;
          }
        }
      })
    }
  }
})

/*
********************* WHITEBOARD *********************
*/
.factory('Whiteboard', function ($rootScope, $resource) {
  return {
    // Create a new whiteboard
    Create: function () {
      return $resource($rootScope.API + 'whiteboard');
    },
    // Delete a whiteboard
    Delete: function () {
      return $resource($rootScope.API + 'whiteboard/:id', { id: "@id" });
    },
    // Delete an object on a whiteboard
    // Replaced by $http request
    // DeleteObject: function () {
    //   return $resource($rootScope.API + 'whiteboard/object/:id', { id: "@id" });
    // },
    // List all whiteboards
    List: function () {
      return $resource($rootScope.API + 'whiteboards/:projectId', { projectId: "@projectId" });
    },
    // Open a whiteboard
    Open: function () {
      return $resource($rootScope.API + 'whiteboard/:id', { id: "@id" });
    },
    // Pull whiteboard modifications
    Pull: function () {
      return $resource($rootScope.API + 'whiteboard/draw/:id', { id: "@id", lastUpdate: "@lastUpdate" });
    },
    // Push whiteboard modification
    Push: function () {
      return $resource($rootScope.API + 'whiteboard/draw/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    Close: function () {
      return $resource($rootScope.API + 'whiteboard/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    }
  }
})

/*
********************* PLANNING *********************
*/
.factory('Planning', function ($rootScope, $resource) {
  return {
    // Get day planning
    Day: function () {
      return $resource($rootScope.API + 'planning/day/:date', { date: "@date" });
    },
    // Get month planning
    Month: function () {
      return $resource($rootScope.API + 'planning/month/:date', { date: "@date" });
    },
    // Get week planning
    Week: function () {
      return $resource($rootScope.API + 'planning/week/:date', { date: "@date" });
    }
  }
})

/*
********************* EVENTS *********************
*/
.factory('Event', function ($rootScope, $resource) {
  return {
    // Get event detail
    Get: function () {
      return $resource($rootScope.API + 'event/:id', { id: "@id" });
    },
    // Edit event
    Edit: function () {
      return $resource($rootScope.API + 'event/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    },
    // Post event
    Create: function () {
      return $resource($rootScope.API + 'event');
    },
    // Delete event
    Delete: function () {
      return $resource($rootScope.API + 'event/:id', { id: "@id" });
    },
    SetUsers: function () {
      return $resource($rootScope.API + 'event/users/:id', { id: "@id" }, {
        'update': { method: 'PUT' }
      });
    }
  }
})

/*
********************* STATISTICS *********************
*/
.factory('Stats', function ($rootScope, $resource) {
  return {
    // Get stat detail
    GetStat: function () {
      return $resource($rootScope.API + 'statistic/:projectId/:statName', { projectId: "@projectId", statName: "@statName" });
    },
    // Get all stats
    GetStats: function () {
      return $resource($rootScope.API + 'statistics/:projectId', { projectId: "@id" });
    }
  }
})

/*
********************* TASKS *********************
*/
// Get current and next tasks
.factory('Tasks', function ($rootScope, $resource) {
  return {
    List: function () {
      return $resource($rootScope.API + 'tasks/project/:projectId', { projectId: "@projectId" });
    },
    Create: function () {
      return $resource($rootScope.API + 'task');
    },
    Get: function () {
      return $resource($rootScope.API + 'task/:taskId', { taskId: "@taskId"});
    },
    Delete: function () {
      return $resource($rootScope.API + 'task/:taskId', { taskId: "@taskId"});
    },
    Edit: function () {
      return $resource($rootScope.API + 'task/:taskId', { taskId: "@taskId"}, {
          'update': { method: 'PUT' }
        });
    }
  }
})
