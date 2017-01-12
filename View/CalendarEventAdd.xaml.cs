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
using System.Collections.ObjectModel;
using Grappbox.Helpers;
using Windows.Web.Http;
using System.Threading.Tasks;
using Grappbox.CustomControls;
using Windows.UI.Popups;
using System.Diagnostics;

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class CalendarEventAdd : Page
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

        public EventViewModel Event { get; private set; } = new EventViewModel()
        {
            Creator = new Creator()
            {
                Id = 0
            },
            ProjectId = 0,
            Users = null,
            BeginDate = DateTime.Today.Date.ToString("yyyy-MM-dd HH:mm:ss"),
            EndDate = DateTime.Today.Date.ToString("yyyy-MM-dd HH:mm:ss"),
            CreatedAt = DateTime.Today.ToString("yyyy-MM-dd HH:mm:ss")
        };
        public CalendarEventAdd()
        {
            this.InitializeComponent();
            SelectedUsers = new ObservableCollection<UserModel>();
        }

        private async Task GetUsersList()
        {
            SessionHelper session = SessionHelper.GetSession();
            if (session.IsProjectSelected == false)
                return;
            object[] values = new object[1];
            values[0] = session.ProjectId;
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

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            await GetUsersList();
            SessionHelper session = SessionHelper.GetSession();
            DateTimeOffset? offset = e.Parameter as DateTimeOffset?;
            Event = new EventViewModel()
            {
                Creator = new Creator()
                {
                    Id = session.UserId
                },
                ProjectId = 0,
                Users = null,
                BeginDate = offset.Value.Date.ToString("yyyy-MM-dd HH:mm:ss"),
                EndDate = offset.Value.AddHours(1).Date.ToString("yyyy-MM-dd HH:mm:ss"),
                CreatedAt = DateTime.Today.ToString("yyyy-MM-dd HH:mm:ss")
            };
            BeginDatePicker.Date = offset;
            EndDatePicker.Date = offset;
            BeginTimePicker.Time = offset.Value.TimeOfDay;
            EndTimePicker.Time = offset.Value.AddHours(1).TimeOfDay;
        }

        private async Task<bool> PostEvent()
        {
            var list = new List<int>();
            if (SelectedUsers != null)
            {
                foreach (var u in SelectedUsers)
                {
                    list.Add(u.Id);
                }
            }
            Dictionary<string, object> values = new Dictionary<string, object>();
            values.Add("title", Event.Title);
            values.Add("description", Event.Description);
            values.Add("begin", Event.BeginDate);
            values.Add("end", Event.EndDate);
            values.Add("users", list);
            if (Event.ProjectId != 0)
                values.Add("projectId", Event.ProjectId);
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Post(values, Constants.PostEvent);
            return res.IsSuccessStatusCode;
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
            sender.Text = string.Empty;
            sender.ItemsSource = Users;
            sender.IsSuggestionListOpen = false;
        }

        private void ParticipantSearch_SuggestionChosen(AutoSuggestBox sender, AutoSuggestBoxSuggestionChosenEventArgs args)
        {
            SelectedUsers.Add((UserModel)args.SelectedItem);
            sender.Text = string.Empty;
            sender.ItemsSource = Users;
            sender.IsSuggestionListOpen = false;
        }
        private void TimePicker_TimeChanged(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.EndDateTime.Date.Year, Event.EndDateTime.Date.Month, Event.EndDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void TimePicker_TimeChanged_1(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.EndDateTime.Date.Year, Event.EndDateTime.Date.Month, Event.EndDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void EndDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date);
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void BeginDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date);
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private async void Save(object sender, RoutedEventArgs e)
        {
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

        private void Title_TextChanged(object sender, TextChangedEventArgs e)
        {
            TextBox tb = sender as TextBox;
            Event.Title = tb.Text;
        }

        private void DeleteParticipant(object sender, RoutedEventArgs e)
        {
            Debug.WriteLine(e.OriginalSource);
        }
    }
}
