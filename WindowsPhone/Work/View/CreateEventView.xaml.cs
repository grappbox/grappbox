using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Model.Global;
using GrappBox.Resources;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.Web.Http;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class CreateEventView : Page
    {
        #region Public Properties
        public DateTime BeginDate
        {
            get { return new DateTime(beginDate.Date.Year, beginDate.Date.Month, beginDate.Date.Day, beginHour.Time.Hours, beginHour.Time.Minutes, 0); ; }
        }
        public DateTime EndDate
        {
            get { return new DateTime(endDate.Date.Year, endDate.Date.Month, endDate.Date.Day, endHour.Time.Hours, endHour.Time.Minutes, 0); }
        }
        private ObservableCollection<ProjectListModel> ProjectList;
        private ObservableCollection<User> UserList;
        #endregion
        private NavigationHelper navigationHelper;
        public CreateEventView()
        {
            this.InitializeComponent();
            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
            this.navigationHelper = new NavigationHelper(this);
        }
        #region NavigationHelper
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            if (await GetProjectList() == true)
            {
                ProjectsComboBox.DisplayMemberPath = "Name";
                ProjectsComboBox.SelectedValuePath = "Id";
                ProjectsComboBox.ItemsSource = ProjectList;
            }
        }
        #endregion
        private async void ConfirmText_Click(object sender, RoutedEventArgs e)
        {
            Event tmp = new Event();
            tmp.BeginDate = DateTimeFormator.DateTimeToDateModel(BeginDate);
            tmp.EndDate = DateTimeFormator.DateTimeToDateModel(EndDate);
            tmp.Creator = new Creator();
            tmp.Creator.Id = User.GetUser().Id;
            tmp.Creator.Fullname = User.GetUser().FullName;
            tmp.Title = title.Text;
            tmp.Description = description.Text;
            int res = await AddEvent(tmp);
            if (res != -1 && UserList != null && UserList.Count > 0)
                await SetParticipant(res);
            this.navigationHelper.GoBack();
        }
        private void CancelText_Click(object sender, RoutedEventArgs e)
        {
            this.navigationHelper.GoBack();
        }
        public async Task<bool> GetProjectList()
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/getprojectsglobalprogress");
            if (res == null)
                return false;
            if (res.IsSuccessStatusCode)
            {
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(await res.Content.ReadAsStringAsync());
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }
        public async Task<bool> GetUsers(int id)
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token, id };
            HttpResponseMessage res = await api.Get(token, "projects/getusertoproject");
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
                try
                {
                    UserList = api.DeserializeArrayJson<ObservableCollection<User>>(json);
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    return false;
                }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }

        public async Task<int> AddEvent(Event evt)
        {
            int projectId = -1;
            if (ProjectsComboBox.SelectedItem != null)
                projectId = (int)ProjectsComboBox.SelectedValue;
            Dictionary<string, object> dict = new Dictionary<string, object>();
            dict.Add("token", User.GetUser().Token);
            if (projectId != -1)
                dict.Add("projectId", projectId);
            dict.Add("title", evt.Title);
            dict.Add("description", evt.Description);
            dict.Add("icon", "");
            dict.Add("typeId", 1);
            dict.Add("begin", evt.BeginDate.date);
            dict.Add("end", evt.EndDate.date);
            Debug.WriteLine(dict.ToString());
            ApiCommunication api = ApiCommunication.Instance;
            HttpResponseMessage res = await api.Post(dict, "event/postevent");
            if (res == null)
                return -1;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                Event tmp = null;
                MessageDialog msgbox = new MessageDialog("Event created");
                await msgbox.ShowAsync();
                try
                {
                    evt = api.DeserializeJson<Event>(json);
                }
                catch(Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    return -1;
                }
                return tmp.Id;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return -1;
            }
        }

        private async Task<bool> SetParticipant(int id)
        {
            ObservableCollection<User> users = new ObservableCollection<User>((IList<User>)UsersListBox.SelectedItems);
            Dictionary<string, object> dict = new Dictionary<string, object>();
            dict.Add("token", User.GetUser().Token);
            dict.Add("eventId", id);
            int[] usersId = new int[users.Count];
            int i = 0;
            JArray intArray = JArray.FromObject(usersId);
            Debug.WriteLine(intArray);
            foreach(User u in users)
                usersId[i] = u.Id;
            dict.Add("toAdd", usersId);
            dict.Add("toRemove", null);
            ApiCommunication api = ApiCommunication.Instance;
            HttpResponseMessage res = await api.Put(dict, "event/setparticipants");
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                MessageDialog msgbox = new MessageDialog("Participants added");
                await msgbox.ShowAsync();
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return false;
            }
        }

        private async void ProjectsComboBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int id = (int)ProjectsComboBox.SelectedValue;
            bool result = await GetUsers(id);
            if (result == true)
            {
                UsersListBox.Visibility = Visibility.Visible;
                UsersListBox.ItemsSource = UserList;
            }
            else
            {
                UsersListBox.Visibility = Visibility.Collapsed;
            }
        }
        private void UsersListBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {

        }

        private void description_TextChanged(object sender, TextChangedEventArgs e)
        {
            if (description.Text.Length != 0 && title.Text.Length != 0)
            {
                ConfirmText.IsEnabled = true;
            }
        }
    }
}
