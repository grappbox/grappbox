using GrappBox.Helpers;
using GrappBox.HttpRequest;
using GrappBox.Model;

using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text.RegularExpressions;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace GrappBox.ViewModel
{
    internal class ProjectSettingsViewModel : ViewModelBase
    {
        static private ProjectSettingsViewModel instance = null;
        private ObservableCollection<CustomerAccessModel> _customerAccessModel = new ObservableCollection<CustomerAccessModel>();
        private ObservableCollection<ProjectRoleModel> _projectRoleModel = new ObservableCollection<ProjectRoleModel>();
        private ProjectSettingsModel _projectSettingsModel = new ProjectSettingsModel();
        private ObservableCollection<UserModel> _projectUserModel = new ObservableCollection<UserModel>();
        private RoleUserModel _roleUser = new RoleUserModel();
        private UserModel _userSelected = new UserModel();
        private CustomerAccessModel _customerSelected = new CustomerAccessModel();
        private ProjectRoleModel _roleSelected = new ProjectRoleModel();
        private ProjectRoleModel _role = new ProjectRoleModel();

        static public ProjectSettingsViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new ProjectSettingsViewModel();
        }

        public ProjectSettingsViewModel()
        {
            instance = this;
            _role = new ProjectRoleModel();
        }

        #region CustomerAccess

        public async System.Threading.Tasks.Task addCustomerAccess(string name)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", name);
            HttpResponseMessage res = await api.Post(props, "project/customeraccess");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel.Clear();
                await getCustomerAccesses();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task regenerateCustomerAccess()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", _customerSelected.Name);
            HttpResponseMessage res = await api.Post(props, "project/customeraccess");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel.Clear();
                await getCustomerAccesses();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getCustomerAccesses()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "project/customeraccesses");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel = HttpRequestManager.DeserializeArrayJson<ObservableCollection<CustomerAccessModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CustomerList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task removeCustomerAccess()
        {
            if (_customerSelected != null)
            {
                HttpRequestManager api = HttpRequestManager.Instance;

                object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen"), _customerSelected.Id };
                HttpResponseMessage res = await api.Delete(token, "project/customeraccess");
                if (res.IsSuccessStatusCode)
                {
                    _customerAccessModel.Remove(_customerSelected);
                    NotifyPropertyChanged("CustomerList");
                    _customerSelected = null;
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<CustomerAccessModel> CustomerList
        {
            get { return _customerAccessModel; }
            set { _customerAccessModel = value; NotifyPropertyChanged("CustomerList"); }
        }

        public CustomerAccessModel CustomerSelected
        {
            set
            {
                if (value != _customerSelected)
                {
                    _customerSelected = value;
                }
            }
        }

        #endregion CustomerAccess

        #region ProjectRole

        public async System.Threading.Tasks.Task<bool> addRole()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", _role.Name);
            props.Add("teamTimeline", _role.TeamTimeline);
            props.Add("customerTimeline", _role.CustomerTimeline);
            props.Add("gantt", _role.Gantt);
            props.Add("whiteboard", _role.Whiteboard);
            props.Add("bugtracker", _role.Bugtracker);
            props.Add("event", _role.Event);
            props.Add("task", _role.Task);
            props.Add("projectSettings", _role.ProjectSettings);
            props.Add("cloud", _role.Cloud);
            HttpResponseMessage res = await api.Post(props, "role");
            if (res.IsSuccessStatusCode)
            {
                await getRoles();
                _role = _projectRoleModel.Last();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        public async System.Threading.Tasks.Task<bool> updateRole()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("name", _role.Name);
            props.Add("teamTimeline", _role.TeamTimeline);
            props.Add("customerTimeline", _role.CustomerTimeline);
            props.Add("gantt", _role.Gantt);
            props.Add("whiteboard", _role.Whiteboard);
            props.Add("bugtracker", _role.Bugtracker);
            props.Add("event", _role.Event);
            props.Add("task", _role.Task);
            props.Add("projectSettings", _role.ProjectSettings);
            props.Add("cloud", _role.Cloud);
            HttpResponseMessage res = await api.Put(props, "role/" + _role.RoleId);
            if (res.IsSuccessStatusCode)
            {
                await getRoles();

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        public void role(ProjectRoleModel roleSelected)
        {
            _role = roleSelected;
            _roleSelected = null;
            notifySimpleRole();
        }

        public async System.Threading.Tasks.Task<bool> assignUserRole(int userId, int roleId)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("roleId", roleId);
            props.Add("userId", userId);
            HttpResponseMessage res = await api.Post(props, "role/user");
            if (res.IsSuccessStatusCode)
            {
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
        }

        public async System.Threading.Tasks.Task<bool> removeUserRole(int userId, int roleId)
        {
            HttpRequestManager api = HttpRequestManager.Instance;

            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen"), userId, roleId };
            HttpResponseMessage res = await api.Delete(token, "role/user");
            if (res.IsSuccessStatusCode)
            {
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
        }

        public void setRole(ProjectRoleModel model)
        {
            _role = model;
        }

        private void notifySimpleRole()
        {
            NotifyPropertyChanged("RoleName");
            NotifyPropertyChanged("TeamTimeline");
            NotifyPropertyChanged("CustomerTimeline");
            NotifyPropertyChanged("Gantt");
            NotifyPropertyChanged("Whiteboard");
            NotifyPropertyChanged("Bugtracker");
            NotifyPropertyChanged("Event");
            NotifyPropertyChanged("Task");
            NotifyPropertyChanged("ProjectSettings");
            NotifyPropertyChanged("Cloud");
        }

        public async System.Threading.Tasks.Task getRoles()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "roles");
            if (res.IsSuccessStatusCode)
            {
                _projectRoleModel = HttpRequestManager.DeserializeArrayJson<ObservableCollection<ProjectRoleModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("RoleList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task removeRole()
        {
            if (_roleSelected != null)
            {
                HttpRequestManager api = HttpRequestManager.Instance;

                object[] token = { _roleSelected.RoleId };
                HttpResponseMessage res = await api.Delete(token, "role");
                if (res.IsSuccessStatusCode)
                {
                    _projectRoleModel.Remove(_roleSelected);
                    NotifyPropertyChanged("RoleList");
                    _roleSelected = null;
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<ProjectRoleModel> RoleList
        {
            get { return _projectRoleModel; }
            set { _projectRoleModel = value; NotifyPropertyChanged("RoleList"); }
        }

        public ProjectRoleModel RoleSelected
        {
            get { return _roleSelected; }
            set
            {
                if (value != _roleSelected)
                {
                    _roleSelected = value;
                }
            }
        }

        public string RoleName
        {
            get { if (_role == null) return ""; string name = _role.Name; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _role.Name)
                {
                    _role.Name = value;
                    NotifyPropertyChanged("RoleName");
                }
            }
        }

        public int TeamTimeline
        {
            get { if (_role == null) return 0; return _role.TeamTimeline; }
            set
            {
                if (value != _role.TeamTimeline)
                {
                    _role.TeamTimeline = value;
                    NotifyPropertyChanged("TeamTimeline");
                }
            }
        }

        public int CustomerTimeline
        {
            get { if (_role == null) return 0; return _role.CustomerTimeline; }
            set
            {
                if (value != _role.CustomerTimeline)
                {
                    _role.CustomerTimeline = value;
                    NotifyPropertyChanged("CustomerTimeline");
                }
            }
        }

        public int Gantt
        {
            get { if (_role == null) return 0; return _role.Gantt; }
            set
            {
                if (value != _role.Gantt)
                {
                    _role.Gantt = value;
                    NotifyPropertyChanged("Gantt");
                }
            }
        }

        public int Whiteboard
        {
            get { if (_role == null) return 0; return _role.Whiteboard; }
            set
            {
                if (value != _role.Whiteboard)
                {
                    _role.Whiteboard = value;
                    NotifyPropertyChanged("Whiteboard");
                }
            }
        }

        public int Bugtracker
        {
            get { if (_role == null) return 0; return _role.Bugtracker; }
            set
            {
                if (value != _role.Bugtracker)
                {
                    _role.Bugtracker = value;
                    NotifyPropertyChanged("Bugtracker");
                }
            }
        }

        public int Event
        {
            get { if (_role == null) return 0; return _role.Event; }
            set
            {
                if (value != _role.Event)
                {
                    _role.Event = value;
                    NotifyPropertyChanged("Event");
                }
            }
        }

        public int Task
        {
            get { if (_role == null) return 0; return _role.Task; }
            set
            {
                if (value != _role.Task)
                {
                    _role.Task = value;
                    NotifyPropertyChanged("Task");
                }
            }
        }

        public int ProjectSettings
        {
            get { if (_role == null) return 0; return _role.ProjectSettings; }
            set
            {
                if (value != _role.ProjectSettings)
                {
                    _role.ProjectSettings = value;
                    NotifyPropertyChanged("ProjectSettings");
                }
            }
        }

        public int Cloud
        {
            get { if (_role == null) return 0; return _role.Cloud; }
            set
            {
                if (value != _role.Cloud)
                {
                    _role.Cloud = value;
                    NotifyPropertyChanged("Cloud");
                }
            }
        }

        #endregion ProjectRole

        #region ProjectSettings

        public async System.Threading.Tasks.Task getProjectLogo()
        {
            //await _projectSettingsModel.LogoUpdate();
            //await _projectSettingsModel.SetLogo();
            //NotifyPropertyChanged("Logo");
        }

        public async System.Threading.Tasks.Task updateProjectSettings(string oldPassword = null, string newPassword = null)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            if (_projectSettingsModel.Name != null && _projectSettingsModel.Name != "")
                props.Add("name", _projectSettingsModel.Name);
            if (_projectSettingsModel.Description != null && _projectSettingsModel.Description != "")
                props.Add("description", _projectSettingsModel.Description);
            if (_projectSettingsModel.LogoString != null && _projectSettingsModel.LogoString != "")
                props.Add("logo", _projectSettingsModel.LogoString);
            if (_projectSettingsModel.Phone != null && _projectSettingsModel.Phone != "")
                props.Add("phone", _projectSettingsModel.Phone);
            if (_projectSettingsModel.Company != null && _projectSettingsModel.Company != "")
                props.Add("company", _projectSettingsModel.Company);
            if (_projectSettingsModel.ContactMail != null && _projectSettingsModel.ContactMail != "")
            {
                Regex regex = new Regex(@"^[\w!#$%&'*+\-/=?\^_`{|}~]+(\.[\w!#$%&'*+\-/=?\^_`{|}~]+)*@((([\-\w]+\.)+[a-zA-Z]{2,4})|(([0-9]{1,3}\.){3}[0-9]{1,3}))\z");
                Match match = regex.Match(_projectSettingsModel.ContactMail);
                if (match.Success)
                    props.Add("email", _projectSettingsModel.ContactMail);
                else
                {
                    MessageDialog msgbox = new MessageDialog("Your email is incorrect");
                    await msgbox.ShowAsync();
                    return;
                }
            }
            if (_projectSettingsModel.Facebook != null && _projectSettingsModel.Facebook != "")
                props.Add("facebook", _projectSettingsModel.Facebook);
            if (_projectSettingsModel.Twitter != null && _projectSettingsModel.Twitter != "")
                props.Add("twitter", _projectSettingsModel.Twitter);
            HttpResponseMessage res = await api.Put(props, "project/" + SettingsManager.getOption<int>("ProjectIdChoosen"));
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task createProject(string password)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            SettingsManager.setOption("ProjectIdChoosen", 0);
            if (_projectSettingsModel.Name != null && _projectSettingsModel.Name != "")
                props.Add("name", _projectSettingsModel.Name);
            else
            {
                MessageDialog msgbox = new MessageDialog("A name is needed for creating a project");
                await msgbox.ShowAsync();
                return;
            }
            if (_projectSettingsModel.Description != null && _projectSettingsModel.Description != "")
                props.Add("description", _projectSettingsModel.Description);
            if (_projectSettingsModel.LogoString != null && _projectSettingsModel.LogoString != "")
                props.Add("logo", _projectSettingsModel.LogoString);
            if (_projectSettingsModel.Phone != null && _projectSettingsModel.Phone != "")
                props.Add("phone", _projectSettingsModel.Phone);
            if (_projectSettingsModel.Company != null && _projectSettingsModel.Company != "")
                props.Add("company", _projectSettingsModel.Company);
            if (_projectSettingsModel.ContactMail != null && _projectSettingsModel.ContactMail != "")
            {
                Regex regex = new Regex(@"^[\w!#$%&'*+\-/=?\^_`{|}~]+(\.[\w!#$%&'*+\-/=?\^_`{|}~]+)*@((([\-\w]+\.)+[a-zA-Z]{2,4})|(([0-9]{1,3}\.){3}[0-9]{1,3}))\z");
                Match match = regex.Match(_projectSettingsModel.ContactMail);
                if (match.Success)
                    props.Add("email", _projectSettingsModel.ContactMail);
                else
                {
                    MessageDialog msgbox = new MessageDialog("Your email is incorrect");
                    await msgbox.ShowAsync();
                    return;
                }
            }
            if (_projectSettingsModel.Facebook != null && _projectSettingsModel.Facebook != "")
                props.Add("facebook", _projectSettingsModel.Facebook);
            if (_projectSettingsModel.Twitter != null && _projectSettingsModel.Twitter != "")
                props.Add("twitter", _projectSettingsModel.Twitter);
            if (password != null && password != "")
                props.Add("password", password);
            else
            {
                MessageDialog msgbox = new MessageDialog("You need to put a password for your cloud's safe folder");
                await msgbox.ShowAsync();
                return;
            }
            HttpResponseMessage res = await api.Post(props, "project");
            if (res.IsSuccessStatusCode)
            {
                _projectSettingsModel = HttpRequestManager.DeserializeJson<ProjectSettingsModel>(await res.Content.ReadAsStringAsync());
                SettingsManager.setOption("ProjectIdChoosen", _projectSettingsModel.Id);
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getProjectSettings()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "project");
            if (res.IsSuccessStatusCode)
            {
                _projectSettingsModel = HttpRequestManager.DeserializeJson<ProjectSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyProjectSettings();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task deleteProject()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Delete(token, "project");
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task retrieveProject()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "project/retrieve");
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        private void notifyProjectSettings()
        {
            NotifyPropertyChanged("Name");
            NotifyPropertyChanged("Description");
            NotifyPropertyChanged("Logo");
            NotifyPropertyChanged("Phone");
            NotifyPropertyChanged("Company");
            NotifyPropertyChanged("ContactMail");
            NotifyPropertyChanged("Facebook");
            NotifyPropertyChanged("Twitter");
            NotifyPropertyChanged("Color");
            NotifyPropertyChanged("CreationDate");
            NotifyPropertyChanged("DeletedAt");
        }

        public string Name
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Name; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Name)
                {
                    _projectSettingsModel.Name = value;
                    NotifyPropertyChanged("Name");
                }
            }
        }

        public string Description
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Description; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Description)
                {
                    _projectSettingsModel.Description = value;
                    NotifyPropertyChanged("Description");
                }
            }
        }

        public BitmapImage Logo
        {
            get
            {
                if (_projectSettingsModel == null)
                    return new BitmapImage();
                return _projectSettingsModel.Logo;
            }
        }

        public string logo
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.LogoString; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.LogoString)
                {
                    _projectSettingsModel.LogoString = value;
                    NotifyPropertyChanged("Logo");
                }
            }
        }

        public string Phone
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Phone; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Phone)
                {
                    _projectSettingsModel.Phone = value;
                    NotifyPropertyChanged("Phone");
                }
            }
        }

        public string Company
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Company; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Company)
                {
                    _projectSettingsModel.Company = value;
                    NotifyPropertyChanged("Company");
                }
            }
        }

        public string ContactMail
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.ContactMail; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.ContactMail)
                {
                    _projectSettingsModel.ContactMail = value;
                    NotifyPropertyChanged("ContactMail");
                }
            }
        }

        public string Facebook
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Facebook; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Facebook)
                {
                    _projectSettingsModel.Facebook = value;
                    NotifyPropertyChanged("Facebook");
                }
            }
        }

        public string Twitter
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Twitter; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Twitter)
                {
                    _projectSettingsModel.Twitter = value;
                    NotifyPropertyChanged("Twitter");
                }
            }
        }

        public string Color
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Color; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Color)
                {
                    _projectSettingsModel.Color = value;
                    NotifyPropertyChanged("Color");
                }
            }
        }

        public DateTime CreationDate
        {
            get { if (_projectSettingsModel == null) return DateTime.Today; DateTime name = DateTime.Parse(_projectSettingsModel.CreatedAt).ToLocalTime(); if (name != null) { return name; } else return DateTime.Today; }
            set
            {
                if (value != DateTime.Parse(_projectSettingsModel.CreatedAt))
                {
                    _projectSettingsModel.CreatedAt = value.ToUniversalTime().ToString("yyyy-MM-dd hh-mm-ss");
                    NotifyPropertyChanged("CreationDate");
                }
            }
        }

        public DateTime DeletedAt
        {
            get { if (_projectSettingsModel == null) return DateTime.MinValue; if (_projectSettingsModel.DeletedAt != null) { return DateTime.Parse(_projectSettingsModel.DeletedAt).ToLocalTime(); } else return DateTime.MinValue; }
            set
            {
                if (value != DateTime.Parse(_projectSettingsModel.DeletedAt))
                {
                    _projectSettingsModel.DeletedAt = value.ToUniversalTime().ToString("yyyy-MM-dd hh:mm:ss");
                    NotifyPropertyChanged("DeletedAt");
                }
            }
        }

        #endregion ProjectSettings

        #region ProjectUser

        public async System.Threading.Tasks.Task addProjectUser(string email)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("id", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("email", email);
            HttpResponseMessage res = await api.Post(props, "project/user");
            if (res.IsSuccessStatusCode)
            {
                UserModel newUser = HttpRequestManager.DeserializeJson<UserModel>(await res.Content.ReadAsStringAsync());
                _projectUserModel.Add(newUser);
                NotifyPropertyChanged("UserList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getProjectUsers()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "project/users");
            if (res.IsSuccessStatusCode)
            {
                _projectUserModel = HttpRequestManager.DeserializeArrayJson<ObservableCollection<UserModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("UserList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task<ProjectRoleModel> getUserRole(int id)
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen"), id };
            HttpResponseMessage res = await api.Get(token, "roles/project/user");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                return HttpRequestManager.DeserializeJson<ProjectRoleModel>(await res.Content.ReadAsStringAsync());
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return null;
            }
        }

        public async System.Threading.Tasks.Task removeProjectUser()
        {
            if (_userSelected != null)
            {
                HttpRequestManager api = HttpRequestManager.Instance;

                object[] token = { SettingsManager.getOption<int>("ProjectIdChoosen"), _userSelected.Id };
                HttpResponseMessage res = await api.Delete(token, "project/user");
                if (res.IsSuccessStatusCode)
                {
                    _projectUserModel.Remove(_userSelected);
                    NotifyPropertyChanged("UserList");
                    _userSelected = null;
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<UserModel> UserList
        {
            get { return _projectUserModel; }
            set { _projectUserModel = value; NotifyPropertyChanged("UserList"); }
        }

        public UserModel UserSelected
        {
            set
            {
                if (value != _userSelected)
                {
                    _userSelected = value;
                }
            }
        }

        #endregion ProjectUser
    }
}