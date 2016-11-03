using GrappBox.HttpRequest;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Helpers
{
    public class AppGlobalHelper
    {
        /// <summary>
        /// User connected to the app
        /// </summary>
        private static User _currentUser;

        /// <summary>
        /// Connected user property
        /// </summary>
        public static User CurrentUser
        {
            get { return _currentUser; }
            set
            {
                _currentUser = value;
                NotifyPropertyChanged("CurrentUser");
                NotifyPropertyChanged("CurrentUserFirstName");
                NotifyPropertyChanged("CurrentUserLastName");
                NotifyPropertyChanged("CurrentUserFullName");
            }
        }

        /// <summary>
        /// User firstname property
        /// </summary>
        public static string CurrentUserFirstName
        {
            get
            {
                if (CurrentUser == null)
                    return "";
                return CurrentUser.Firstname;
            }
        }

        /// <summary>
        /// User lastname property
        /// </summary>
        public static string CurrentUserLastName
        {
            get
            {
                if (CurrentUser == null)
                    return "";
                return CurrentUser.Lastname;
            }
        }

        /// <summary>
        /// User firstname property
        /// </summary>
        public static string CurrentUserFullName
        {
            get
            {
                if (CurrentUser == null)
                    return "";
                return CurrentUser.FullName;
            }
        }

        /// <summary>
        /// Id of the current project of the application
        /// </summary>
        public static int ProjectId = -1;

        /// <summary>
        /// Name of the current project of the application
        /// </summary>
        public static string ProjectName = null;

        public static event PropertyChangedEventHandler PropertyChanged;

        public static void NotifyPropertyChanged(string property)
        {
            Debug.WriteLine("toto");
            if (PropertyChanged != null)
            {
                PropertyChanged(typeof(AppGlobalHelper), new PropertyChangedEventArgs(property));
            }
        }
    }
}