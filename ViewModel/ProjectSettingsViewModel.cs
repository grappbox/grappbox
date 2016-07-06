using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Ressources;
using GrappBox.View;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.ViewModel
{
    class ProjectSettingsViewModel : ViewModelBase
    {
        static private ProjectSettingsViewModel instance = null;
        private ObservableCollection<CustomerAccessModel> _customerAccessModel = new ObservableCollection<CustomerAccessModel>();
        private ObservableCollection<ProjectRoleModel> _projectRoleModel = new ObservableCollection<ProjectRoleModel>();
        private ProjectSettingsModel _projectSettingsModel = new ProjectSettingsModel();
        private ObservableCollection<ProjectUserModel> _projectUserModel = new ObservableCollection<ProjectUserModel>();
        private ObservableCollection<ProjectUserModel> _userAssigned = new ObservableCollection<ProjectUserModel>();
        private ObservableCollection<ProjectUserModel> _userNonAssigned = new ObservableCollection<ProjectUserModel>();
        private RoleUserModel _roleUser = new RoleUserModel();
        private ProjectUserModel _userSelected = new ProjectUserModel();
        private ProjectUserModel _userAssignSelected = new ProjectUserModel();
        private ProjectUserModel _userNonAssignSelected = new ProjectUserModel();
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
        }

        #region CustomerAccess
        public async System.Threading.Tasks.Task addCustomerAccess(string name)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", name);
            HttpResponseMessage res = await api.Post(props, "projects/generatecustomeraccess");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel.Clear();
                await getCustomerAccesses();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task regenerateCustomerAccess()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", _customerSelected.Name);
            HttpResponseMessage res = await api.Post(props, "projects/generatecustomeraccess");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel.Clear();
                await getCustomerAccesses();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getCustomerAccesses()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "projects/getcustomeraccessbyproject");
            if (res.IsSuccessStatusCode)
            {
                _customerAccessModel = api.DeserializeArrayJson<ObservableCollection<CustomerAccessModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CustomerList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task removeCustomerAccess()
        {
            if (_customerSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();

                object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _customerSelected.Id };
                HttpResponseMessage res = await api.Delete(token, "projects/delcustomeraccess");
                if (res.IsSuccessStatusCode)
                {
                    _customerAccessModel.Remove(_customerSelected);
                    NotifyPropertyChanged("CustomerList");
                    _customerSelected = null;
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<CustomerAccessModel> CustomerList
        {
            get { return _customerAccessModel; }
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
        public async System.Threading.Tasks.Task addRole()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
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
            HttpResponseMessage res = await api.Post(props, "roles/addprojectroles");
            if (res.IsSuccessStatusCode)
            {
                _projectRoleModel.Clear();
                await getRoles();
                _role = _projectRoleModel.Last();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task updateRole()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("roleId", _role.Id);
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
            HttpResponseMessage res = await api.Put(props, "roles/putprojectroles");
            if (res.IsSuccessStatusCode)
            {
                _projectRoleModel.Clear();
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
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public void getRole()
        {
            _role = _roleSelected;
            _roleSelected = null;
            notifySimpleRole();
        }

        public async System.Threading.Tasks.Task getUsersAssigned(int id)
        {
            if (id == 0)
            {
                _userNonAssigned = _projectUserModel;
                NotifyPropertyChanged("UserAssignedList");
            }
            else
            {
                ApiCommunication api = ApiCommunication.GetInstance();
                object[] token = { User.GetUser().Token, id };
                HttpResponseMessage res = await api.Get(token, "roles/getusersforrole");
                if (res.IsSuccessStatusCode)
                {
                    _roleUser = api.DeserializeJson<RoleUserModel>(await res.Content.ReadAsStringAsync());
                    _userAssigned = new ObservableCollection<ProjectUserModel>(_roleUser.UsersAssigned);
                    _userNonAssigned = new ObservableCollection<ProjectUserModel>(_roleUser.UsersNonAssigned);
                    NotifyPropertyChanged("UserAssignedList");
                    NotifyPropertyChanged("UserNonAssignedList");
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public async System.Threading.Tasks.Task assignUserRole()
        {
            if (_userNonAssignSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();
                Dictionary<string, object> props = new Dictionary<string, object>();

                props.Add("token", User.GetUser().Token);
                props.Add("roleId", _role.Id);
                props.Add("userId", _userNonAssignSelected.Id);
                HttpResponseMessage res = await api.Post(props, "roles/assignpersontorole");
                if (res.IsSuccessStatusCode)
                {
                    _userAssigned.Add(_userNonAssignSelected);
                    _userNonAssigned.Remove(_userNonAssignSelected);
                    _userNonAssignSelected = null;
                    NotifyPropertyChanged("UserAssignedList");
                    NotifyPropertyChanged("UserNonAssignedList");
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
                props.Clear();
            }
        }

        public async System.Threading.Tasks.Task removeUserRole()
        {
            if (_userAssignSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();

                object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _userAssignSelected.Id, _role.Id };
                HttpResponseMessage res = await api.Delete(token, "roles/delpersonrole");
                if (res.IsSuccessStatusCode)
                {
                    _userNonAssigned.Add(_userAssignSelected);
                    _userAssigned.Remove(_userAssignSelected);
                    _userAssignSelected = null;
                    NotifyPropertyChanged("UserAssignedList");
                    NotifyPropertyChanged("UserNonAssignedList");
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
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
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "roles/getprojectroles");
            if (res.IsSuccessStatusCode)
            {
                _projectRoleModel = api.DeserializeArrayJson<ObservableCollection<ProjectRoleModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("RoleList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task removeRole()
        {
            if (_roleSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();

                object[] token = { User.GetUser().Token, _roleSelected.Id };
                HttpResponseMessage res = await api.Delete(token, "roles/delprojectroles");
                if (res.IsSuccessStatusCode)
                {
                    _projectRoleModel.Remove(_roleSelected);
                    NotifyPropertyChanged("RoleList");
                    _roleSelected = null;
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<ProjectRoleModel> RoleList
        {
            get { return _projectRoleModel; }
        }

        public ObservableCollection<ProjectUserModel> UserAssignedList
        {
            get { return _userAssigned; }
        }

        public ObservableCollection<ProjectUserModel> UserNonAssignedList
        {
            get { return _userNonAssigned; }
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

        public ProjectUserModel UserAssignedSelected
        {
            get { return _userAssignSelected; }
            set
            {
                if (value != _userAssignSelected)
                {
                    _userAssignSelected = value;
                }
            }
        }

        public ProjectUserModel UserNonAssignedSelected
        {
            get { return _userNonAssignSelected; }
            set
            {
                if (value != _userNonAssignSelected)
                {
                    _userNonAssignSelected = value;
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
        public async System.Threading.Tasks.Task updateProjectSettings(string oldPassword = null, string newPassword = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            if (_projectSettingsModel.Name != null && _projectSettingsModel.Name != "")
                props.Add("name", _projectSettingsModel.Name);
            if (_projectSettingsModel.Description != null && _projectSettingsModel.Description != "")
                props.Add("description", _projectSettingsModel.Description);
            if (_projectSettingsModel.Logo != null && _projectSettingsModel.Logo != "")
                props.Add("logo", _projectSettingsModel.Logo);
            if (_projectSettingsModel.Phone != null && _projectSettingsModel.Phone != "")
                props.Add("phone", _projectSettingsModel.Phone);
            if (_projectSettingsModel.Company != null && _projectSettingsModel.Company != "")
                props.Add("company", _projectSettingsModel.Company);
            if (_projectSettingsModel.ContactMail != null && _projectSettingsModel.ContactMail != "")
                props.Add("email", _projectSettingsModel.ContactMail);
            if (_projectSettingsModel.Facebook != null && _projectSettingsModel.Facebook != "")
                props.Add("facebook", _projectSettingsModel.Facebook);
            if (_projectSettingsModel.Twitter != null && _projectSettingsModel.Twitter != "")
                props.Add("twitter", _projectSettingsModel.Twitter);
            HttpResponseMessage res = await api.Put(props, "projects/updateinformations");
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
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task createProject(string password)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            SettingsManager.setOption("ProjectIdChoosen", 0);
            props.Add("token", User.GetUser().Token);
            if (_projectSettingsModel.Name != null && _projectSettingsModel.Name != "")
                props.Add("name", _projectSettingsModel.Name);
            else
            {
                MessageDialog msgbox = new MessageDialog("A man needs a name!");
                await msgbox.ShowAsync();
                return;
            }
            if (_projectSettingsModel.Description != null && _projectSettingsModel.Description != "")
                props.Add("description", _projectSettingsModel.Description);
            if (_projectSettingsModel.Logo != null && _projectSettingsModel.Logo != "")
                props.Add("logo", _projectSettingsModel.Logo);
            if (_projectSettingsModel.Phone != null && _projectSettingsModel.Phone != "")
                props.Add("phone", _projectSettingsModel.Phone);
            if (_projectSettingsModel.Company != null && _projectSettingsModel.Company != "")
                props.Add("company", _projectSettingsModel.Company);
            if (_projectSettingsModel.ContactMail != null && _projectSettingsModel.ContactMail != "")
                props.Add("email", _projectSettingsModel.ContactMail);
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
            HttpResponseMessage res = await api.Post(props, "projects/projectcreation");
            if (res.IsSuccessStatusCode)
            {
                _projectSettingsModel = api.DeserializeJson<ProjectSettingsModel>(await res.Content.ReadAsStringAsync());
                SettingsManager.setOption("ProjectIdChoosen", _projectSettingsModel.Id);
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

        public async System.Threading.Tasks.Task getProjectSettings()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "projects/getinformations");
            if (res.IsSuccessStatusCode)
            {
                _projectSettingsModel = api.DeserializeJson<ProjectSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyProjectSettings();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task deleteProject()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Delete(token, "projects/delproject");
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
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task retrieveProject()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "projects/retrieveproject");
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
            else {
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
                string base64 = _projectSettingsModel.Logo;
                if (base64 == null || base64 == "")
                    return new BitmapImage();
                else
                {
                    var imageBytes = Convert.FromBase64String(base64);
                    using (InMemoryRandomAccessStream ms = new InMemoryRandomAccessStream())
                    {
                        using (DataWriter writer = new DataWriter(ms.GetOutputStreamAt(0)))
                        {
                            writer.WriteBytes((byte[])imageBytes);
                            writer.StoreAsync().GetResults();
                        }

                        var image = new BitmapImage();
                        image.SetSource(ms);
                        return image;
                    }
                }
            }
        }

        public string logo
        {
            get { if (_projectSettingsModel == null) return ""; string name = _projectSettingsModel.Logo; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _projectSettingsModel.Logo)
                {
                    _projectSettingsModel.Logo = value;
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
            get { if (_projectSettingsModel == null) return DateTime.Today; DateTime name = DateTime.Parse(_projectSettingsModel.CreationDate.date); if (name != null) { return name; } else return DateTime.Today; }
            set
            {
                if (value != DateTime.Parse(_projectSettingsModel.CreationDate.date))
                {
                    _projectSettingsModel.CreationDate.date = value.ToString("yyyy-MM-dd hh-mm-ss");
                    NotifyPropertyChanged("CreationDate");
                }
            }
        }

        public DateTime DeletedAt
        {
            get { if (_projectSettingsModel == null) return DateTime.MinValue; if (_projectSettingsModel.DeletedAt != null) { return DateTime.Parse(_projectSettingsModel.DeletedAt.date); } else return DateTime.MinValue; }
            set
            {
                if (value != DateTime.Parse(_projectSettingsModel.DeletedAt.date))
                {
                    _projectSettingsModel.DeletedAt.date = value.ToString("yyyy-MM-dd hh:mm:ss");
                    NotifyPropertyChanged("DeletedAt");
                }
            }
        }

        #endregion ProjectSettings

        #region ProjectUser
        public async System.Threading.Tasks.Task addProjectUser(string email)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("id", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("email", email);
            HttpResponseMessage res = await api.Post(props, "projects/addusertoproject");
            if (res.IsSuccessStatusCode)
            {
                ProjectUserModel newUser = api.DeserializeJson<ProjectUserModel>(await res.Content.ReadAsStringAsync());
                _projectUserModel.Add(newUser);
                NotifyPropertyChanged("UserList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getProjectUsers()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "projects/getusertoproject");
            if (res.IsSuccessStatusCode)
            {
                _projectUserModel = api.DeserializeArrayJson<ObservableCollection<ProjectUserModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("UserList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task removeProjectUser()
        {
            if (_userSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();

                object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _userSelected.Id };
                HttpResponseMessage res = await api.Delete(token, "projects/removeusertoproject");
                if (res.IsSuccessStatusCode)
                {
                    _projectUserModel.Remove(_userSelected);
                    NotifyPropertyChanged("UserList");
                    _userSelected = null;
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public ObservableCollection<ProjectUserModel> UserList
        {
            get { return _projectUserModel; }
        }

        public ProjectUserModel UserSelected
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
