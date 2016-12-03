using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Helpers
{
    class SessionHelper
    {
        #region Singleton

        private static SessionHelper _instance = null;
        public static SessionHelper GetSession()
        {
            return _instance ?? CreateSessionHelper();
        }
        public static SessionHelper CreateSessionHelper(UserModel user, ProjectListModel project)
        {
            _instance = new SessionHelper()
            {
                User = new UserViewModel(user),
                Project = new ProjectViewModel(project)
            };
            return _instance;
        }
        public static SessionHelper CreateSessionHelper(UserModel user)
        {
            _instance = new SessionHelper()
            {
                User = new UserViewModel(user),
                Project = _instance?.Project
            };
            return _instance;
        }
        public static SessionHelper CreateSessionHelper(ProjectListModel project)
        {
            _instance = new SessionHelper()
            {
                User = _instance.User,
                Project = new ProjectViewModel(project),
            };
            return _instance;
        }
        public static SessionHelper CreateSessionHelper()
        {
            return _instance ?? new SessionHelper();
        }
        static SessionHelper() { }
        #endregion

        UserViewModel User = null;
        ProjectViewModel Project = null;

        public bool IsUserConnected
        {
            get { return User != null; }
        }

        public bool IsProjectSelected
        {
            get { return Project != null; }
        }

        public string UserToken
        {
            get { return User?.Token; }
        }

        public int UserId
        {
            get { return User != null ? User.Id : -1; }
        }

        public int ProjectId
        {
            get { return Project != null ? Project.Id : -1; }
        }
        public string UserIdString
        {
            get { return Convert.ToString(User?.Id); }
        }

        public string UserName
        {
            get { return User.FullName; }
        }

        public string ProjectIdString
        {
            get { return Convert.ToString(Project?.Id); }
        }
    }
}