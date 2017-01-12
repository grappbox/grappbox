using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.Model;
using Grappbox.ViewModel;
using System.Threading.Tasks;
using Windows.UI.Popups;
using System.Collections.ObjectModel;
using Grappbox.Helpers;
using Windows.Web.Http;
using Grappbox.CustomControls;
using System.Diagnostics;

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class CalendarEventDetail : Page
    {
        private List<UserModel> _users;
        public List<UserModel> Users
        {
            get
            {
                return _users;
            }
            set
            {
                _users = value;
            }
        }

        private ObservableCollection<UserModel> _selectedUsers;
        public ObservableCollection<UserModel> SelectedUsers
        {
            get
            {
                return _selectedUsers;
            }
            set
            {
                _selectedUsers = value;
            }
        }
        public EventViewModel Event { get; private set; }
        public CalendarEventDetail()
        {
            this.InitializeComponent();
        }

        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            if (e.Parameter == null)
                return;
            var evt = e.Parameter as EventViewModel;
            Event = evt;
            if (Event.ProjectId != null)
            {
                ParticipantSearch.Visibility = Visibility.Visible;
                await GetUsersList(Event.ProjectId);
            }
            DateTimeOffset begin = evt.BeginDateTime;
            DateTimeOffset end = evt.EndDateTime;
            BeginDatePicker.Date = begin;
            EndDatePicker.Date = end;
            BeginTimePicker.Time = begin.TimeOfDay;
            EndTimePicker.Time = end.TimeOfDay;
            SelectedUsers = new ObservableCollection<UserModel>(Event.Users);
        }

        private async Task GetUsersList(int? projectId)
        {
            SessionHelper session = SessionHelper.GetSession();
            if (session.IsProjectSelected == false)
                return;
            object[] values = new object[1];
            values[0] = projectId;
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Get(values, Constants.GetProjectUsers);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                var list = SerializationHelper.DeserializeArrayJson<List<UserModel>>(json);
                Users = new List<UserModel>(list);
                ParticipantSearch.ItemsSource = Users;
                ParticipantSearch.DisplayMemberPath = "FullName";
            }
            else
            {
                Users = new List<UserModel>();
                Users.Add(new UserModel()
                {
                    Id = session.UserId,
                    IsClient = false,
                });
            }
        }

        private void ParticipantSearch_TextChanged(AutoSuggestBox sender, AutoSuggestBoxTextChangedEventArgs args)
        {
            if (args.Reason == AutoSuggestionBoxTextChangeReason.UserInput)
            {
                if (string.IsNullOrWhiteSpace(sender.Text))
                    sender.ItemsSource = Users;
                else
                    sender.ItemsSource = Users.Where(u => u.FullName.Contains(sender.Text));
            }
        }

        private void ParticipantSearch_QuerySubmitted(AutoSuggestBox sender, AutoSuggestBoxQuerySubmittedEventArgs args)
        {
        }

        private void ParticipantSearch_SuggestionChosen(AutoSuggestBox sender, AutoSuggestBoxSuggestionChosenEventArgs args)
        {
            SelectedUsers.Add((UserModel)args.SelectedItem);
            sender.Text = string.Empty;
            sender.ItemsSource = Users;
            sender.IsSuggestionListOpen = false;
        }

        private void BeginTimePicker_TimeChanged(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.BeginDateTime.Date.Year, Event.BeginDateTime.Date.Month, Event.BeginDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void EndTimePicker_TimeChanged(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.EndDateTime.Date.Year, Event.EndDateTime.Date.Month, Event.EndDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void EndDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date.Year, args.NewDate.Value.Date.Month, args.NewDate.Value.Date.Day, EndTimePicker.Time.Hours,
                EndTimePicker.Time.Minutes, EndTimePicker.Time.Seconds, new TimeSpan(1, 0, 0));
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void BeginDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date.Year, args.NewDate.Value.Date.Month, args.NewDate.Value.Date.Day, BeginTimePicker.Time.Hours,
                BeginTimePicker.Time.Minutes, BeginTimePicker.Time.Seconds, new TimeSpan(1, 0, 0));
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private async Task<bool> CheckData()
        {
            bool result = true;
            MessageDialog dialog = new MessageDialog("");
            if (string.IsNullOrWhiteSpace(Event.Title))
            {
                dialog.Content = "Title is required";
                result = false;
            }
            else if (string.IsNullOrWhiteSpace(Event.Description))
            {
                dialog.Content = "Description is required";
                result = false;
            }
            else if (DateTime.Compare(Event.EndDateTime, Event.BeginDateTime) < 0)
            {
                dialog.Content = "Event can't start after the end";
                result = false;
            }
            else if (DateTime.Compare(Event.EndDateTime, Event.BeginDateTime) == 0)
            {
                dialog.Content = "Event must have a duration of at least 1 minute";
                result = false;
            }
            if (result == false)
                await dialog.ShowAsync();
            return result;
        }

        private async Task<bool> PostEvent()
        {
            var session = SessionHelper.GetSession();
            var userList = new List<UserModel>();
            userList = this.Event.Users.Where(u => !SelectedUsers.Contains(u)).ToList();
            var list = new List<int>();
            if (userList != null)
            {
                foreach (var u in userList)
                {
                    list.Add(u.Id);
                }
            }
            Dictionary <string, object> values = new Dictionary<string, object>();
            values.Add("title", Event.Title);
            values.Add("description", Event.Description);
            values.Add("begin", Event.BeginDate);
            values.Add("end", Event.EndDate);
            values.Add("toAddUsers", list);
//            values.Add("toRemoveUsers", null);
            if (Event.ProjectId != null)
            {
                values.Add("projectId", (int)Event.ProjectId);
            }
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Post(values, Constants.EditEvent + "/" + Event.Id);
            return res.IsSuccessStatusCode;
        }

        private async void Save(object sender, RoutedEventArgs e)
        {
            Event.Title = Title.Text;
            if (await CheckData() == false)
                return;
            LoaderDialog loader = new LoaderDialog();
            loader.ShowAsync();
            bool res = await PostEvent();
            loader.Hide();
            if (res == true)
            {
                MessageDialog dialog = new MessageDialog("Success");
                await dialog.ShowAsync();
            }
            if (this.Frame.CanGoBack == true)
                this.Frame.GoBack();
        }

        private void Cancel(object sender, RoutedEventArgs e)
        {
            if (this.Frame.CanGoBack == true)
                this.Frame.GoBack();
        }

        private void DeleteParticipant(object sender, RoutedEventArgs e)
        {
            Debug.WriteLine(e.OriginalSource);
        }
    }
}
