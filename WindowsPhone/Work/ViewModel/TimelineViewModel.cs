using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Ressources;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class TimelineViewModel : ViewModelBase
    {
        static private TimelineViewModel instance = null;
        private TimelineListModel _Customer = new TimelineListModel();
        private TimelineListModel _Team = new TimelineListModel();
        private ObservableCollection<TimelineModel> _CustomerMessages;
        private ObservableCollection<TimelineModel> _TeamMessages;
        private ObservableCollection<TimelineListModel> _Timelines;
        private ObservableCollection<TimelineModel> _Comments;
        private TimelineModel _messageSelected = new TimelineModel();
        private TimelineModel _commentSelected = new TimelineModel();

        static public TimelineViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new TimelineViewModel();
        }
        public TimelineViewModel()
        {
            instance = this;
        }

        #region API
        public async System.Threading.Tasks.Task getTeamMessages()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, _Team.Id };
            HttpResponseMessage res = await api.Get(token, "timeline/getmessages");
            if (res.IsSuccessStatusCode)
            {
                _TeamMessages = api.DeserializeArrayJson<ObservableCollection<TimelineModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("TeamList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getCustomerMessages()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, _Customer.Id };
            HttpResponseMessage res = await api.Get(token, "timeline/getmessages");
            if (res.IsSuccessStatusCode)
            {
                _CustomerMessages = api.DeserializeArrayJson<ObservableCollection<TimelineModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CustomerList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getTimelines()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "timeline/gettimelines");
            if (res.IsSuccessStatusCode)
            {
                _Timelines = api.DeserializeArrayJson<ObservableCollection<TimelineListModel>>(await res.Content.ReadAsStringAsync());
                foreach (var item in _Timelines)
                {
                    if (item.TypeName == "customerTimeline")
                    {
                        _Customer = item;
                        NotifyPropertyChanged("CustomerName");
                    }
                    else if (item.TypeName == "teamTimeline")
                    {
                        _Team = item;
                        NotifyPropertyChanged("TeamName");
                    }
                }
                NotifyPropertyChanged("Timelines");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getComments(int timelineId, int messageId)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, timelineId, messageId };
            HttpResponseMessage res = await api.Get(token, "timeline/getcomments");
            if (res.IsSuccessStatusCode)
            {
                if (_Comments != null && _Comments.Count != 0)
                    _Comments.Clear();
                _Comments = api.DeserializeArrayJson<ObservableCollection<TimelineModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("Comments");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task postMessage(int timelineId, string title, string message, int commentedId = 0)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("title", title);
            props.Add("message", message);
            if (commentedId != 0)
                props.Add("commentedId", commentedId);
            HttpResponseMessage res = await api.Post(props, "timeline/postmessage/" + timelineId);
            if (res.IsSuccessStatusCode)
            {
                if (commentedId != 0)
                    await getComments(timelineId, commentedId);
                else
                {
                    if (timelineId == _Customer.Id)
                    {
                        _CustomerMessages.Clear();
                        await getCustomerMessages();
                    }
                    else if (timelineId == _Team.Id)
                    {
                        _TeamMessages.Clear();
                        await getTeamMessages();
                    }
                }
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task removeMessage(TimelineModel message)
        {
            if (_messageSelected != null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();

                object[] token = { User.GetUser().Token, message.TimelineId, message.Id };
                HttpResponseMessage res = await api.Delete(token, "timeline/archivemessage");
                if (res.IsSuccessStatusCode)
                {
                    _CustomerMessages.Remove(message);
                    _TeamMessages.Remove(message);
                    if (_Comments != null)
                        _Comments.Remove(message);
                    if (_messageSelected == message)
                        _messageSelected = null;
                    else if (_commentSelected == message)
                        _commentSelected = null;
                    NotifyPropertyChanged("CustomerList");
                    NotifyPropertyChanged("TeamList");
                    NotifyPropertyChanged("CommentList");
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }

        public async System.Threading.Tasks.Task updateMessage(TimelineModel message)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("messageId", message.Id);
            props.Add("title", message.Title);
            props.Add("message", message.Message);
            HttpResponseMessage res = await api.Put(props, "timeline/editmessage/" + message.TimelineId);
            if (res.IsSuccessStatusCode)
            {
                if (message.ParentId == 0)
                {
                    _CustomerMessages.Clear();
                    _TeamMessages.Clear();
                    await getCustomerMessages();
                    await getTeamMessages();
                }
                else
                    await getComments(message.TimelineId, message.ParentId);

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
        #endregion

        #region Observable Collection
        public ObservableCollection<TimelineModel> CustomerList
        {
            get { return _CustomerMessages; }
        }

        public ObservableCollection<TimelineModel> TeamList
        {
            get { return _TeamMessages; }
        }

        public ObservableCollection<TimelineListModel> Timelines
        {
            get { return _Timelines; }
        }

        public ObservableCollection<TimelineModel> Comments
        {
            get { return _Comments; }
        }
        #endregion

        #region Select
        public TimelineModel MessageSelected
        {
            get { return _messageSelected; }
            set { _messageSelected = value; }
        }

        public TimelineModel CommentSelected
        {
            get { return _commentSelected; }
            set { _commentSelected = value; }
        }
        #endregion

        #region Model
        public int Id
        {
            get { return _messageSelected.Id; }
        }

        public Creator creator
        {
            get { return _messageSelected.Creator; }
        }

        public int TimelineId
        {
            get { return _messageSelected.TimelineId; }
        }

        public string Title
        {
            get { if (_messageSelected == null) return ""; string name = _messageSelected.Title; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _messageSelected.Title)
                {
                    _messageSelected.Title = value;
                    NotifyPropertyChanged("Title");
                }
            }
        }

        public string Message
        {
            get { if (_messageSelected == null) return ""; string name = _messageSelected.Message; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _messageSelected.Message)
                {
                    _messageSelected.Message = value;
                    NotifyPropertyChanged("Message");
                }
            }
        }
        public string TextDate
        {
            get { if (_messageSelected == null) return ""; return _messageSelected.TextDate; }
        }

        public int ParentId
        {
            get { return _messageSelected.ParentId; }
        }

        public DateTime CreationDate
        {
            get { if (_messageSelected == null) return DateTime.Today; DateTime name = DateTime.Parse(_messageSelected.CreatedAt.date); if (name != null) { return name; } else return DateTime.Today; }
        }

        public DateTime EditionDate
        {
            get { if (_messageSelected == null) return DateTime.Today; DateTime name = DateTime.Parse(_messageSelected.EditedAt.date); if (name != null) { return name; } else return DateTime.Today; }
        }
        #endregion

        public string CustomerName
        {
            get { if (_Customer == null) return ""; string name = _Customer.Name; if (name != null) { return name; } else return ""; }
        }

        public string TeamName
        {
            get { if (_Team == null) return ""; string name = _Team.Name; if (name != null) { return name; } else return ""; }
        }

        public int TeamId
        {
            get { if (_Team == null) return 0; return _Team.Id; }
        }

        public int CustomerId
        {
            get { if (_Customer == null) return 0; return _Customer.Id; }
        }
    }
}
