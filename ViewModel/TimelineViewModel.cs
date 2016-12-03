﻿using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Data;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class TimelineViewModel : ViewModelBase
    {
        static private TimelineViewModel instance = null;
        private TimelineListModel _Customer = new TimelineListModel();
        private TimelineListModel _Team = new TimelineListModel();
        private ObservableCollection<TimelineModel> _CustomerMessages = new ObservableCollection<TimelineModel>();
        private ObservableCollection<TimelineModel> _TeamMessages = new ObservableCollection<TimelineModel>();
        private ObservableCollection<TimelineListModel> _Timelines;
        private ObservableCollection<TimelineModel> _Comments;
        private TimelineModel _messageSelected = new TimelineModel();
        private TimelineModel _commentSelected = new TimelineModel();
        public int TeamOffset = 0;
        public int CustomerOffset = 0;
        private int _incrementation = 5;

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

        public async System.Threading.Tasks.Task<bool> getTeamMessages()
        {
            if (_Team.Id != 0)
            {
                int offset = TeamOffset;
                TeamOffset += _incrementation;
                object[] token = { _Team.Id };
                HttpResponseMessage res = await HttpRequestManager.Get(token, "timeline/messages");
                if (res == null)
                {
                    TeamOffset -= _incrementation;
                    return false;
                }
                string json = await res.Content.ReadAsStringAsync();
                if (res.IsSuccessStatusCode)
                {
                    ObservableCollection<TimelineModel> newMessages = SerializationHelper.DeserializeArrayJson<ObservableCollection<TimelineModel>>(await res.Content.ReadAsStringAsync());
                    if (newMessages.Count <= 0 && TeamOffset > (1 + _incrementation))
                    {
                        TeamOffset -= _incrementation;
                        return false;
                    }
                    foreach (TimelineModel item in newMessages)
                    {
                        _TeamMessages.Add(item);
                    }
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(json));
                    await msgbox.ShowAsync();
                    TeamOffset -= _incrementation;
                    return false;
                }
            }
            return true;
        }

        public async System.Threading.Tasks.Task<bool> getCustomerMessages()
        {
            if (_Customer.Id != 0)
            {
                int offset = CustomerOffset;
                CustomerOffset += _incrementation;
                object[] token = { _Customer.Id };
                HttpResponseMessage res = await HttpRequestManager.Get(token, "timeline/messages");
                if (res == null)
                {
                    CustomerOffset -= _incrementation;
                    return false;
                }
                string json = await res.Content.ReadAsStringAsync();
                if (res.IsSuccessStatusCode)
                {
                    ObservableCollection<TimelineModel> newMessages = SerializationHelper.DeserializeArrayJson<ObservableCollection<TimelineModel>>(await res.Content.ReadAsStringAsync());
                    if (newMessages.Count <= 0 && CustomerOffset > 0 + _incrementation)
                    {
                        TeamOffset -= _incrementation;
                        return false;
                    }
                    foreach (TimelineModel item in newMessages)
                    {
                        _CustomerMessages.Add(item);
                    }
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(json));
                    await msgbox.ShowAsync();
                    CustomerOffset -= _incrementation;
                    return false;
                }
            }
            return true;
        }

        public async System.Threading.Tasks.Task<bool> getTimelines()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "timelines");
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                _Timelines = SerializationHelper.DeserializeArrayJson<ObservableCollection<TimelineListModel>>(json);
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
            else
            {
                string error = HttpRequestManager.GetErrorMessage(json);
                MessageDialog msgbox = new MessageDialog(error);
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }

        public async System.Threading.Tasks.Task<bool> getComments(int timelineId, int messageId)
        {
            object[] token = { timelineId, messageId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "timeline/message/comments");
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                if (_Comments != null && _Comments.Count != 0)
                    _Comments.Clear();
                _Comments = SerializationHelper.DeserializeArrayJson<ObservableCollection<TimelineModel>>(json);
                NotifyPropertyChanged("Comments");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }

        public async System.Threading.Tasks.Task<bool> postMessage(int timelineId, string title, string message)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("title", title);
            props.Add("message", message);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "timeline/message/" + timelineId);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                if (timelineId == _Customer.Id)
                {
                    _CustomerMessages.Insert(0, SerializationHelper.DeserializeJson<TimelineModel>(json));
                }
                else if (timelineId == _Team.Id)
                {
                    _TeamMessages.Insert(0, SerializationHelper.DeserializeJson<TimelineModel>(json));
                }
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        public async System.Threading.Tasks.Task<bool> postComment(int timelineId, string message, int commentedId)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("commentedId", commentedId);
            props.Add("comment", message);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "timeline/comment/" + timelineId);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                _Comments.Add(SerializationHelper.DeserializeJson<TimelineModel>(json));
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        public async System.Threading.Tasks.Task<bool> removeMessage(TimelineModel message)
        {
            if (_messageSelected != null)
            {
                object[] token = { message.TimelineId, message.Id };
                HttpResponseMessage res = await HttpRequestManager.Delete(token, "timeline/message");
                if (res == null)
                    return false;
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
                else
                {
                    MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                    return false;
                }
                return true;
            }
            return false;
        }

        public async System.Threading.Tasks.Task<bool> removeComment(TimelineModel message)
        {
            if (_commentSelected != null)
            {
                object[] token = { message.Id };
                HttpResponseMessage res = await HttpRequestManager.Delete(token, "timeline/comment");
                if (res == null)
                    return false;
                if (res.IsSuccessStatusCode)
                {
                    _Comments.Remove(message);
                    if (_commentSelected == message)
                        _commentSelected = null;
                    NotifyPropertyChanged("CommentList");
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                    return false;
                }
                return true;
            }
            return false;
        }

        public async System.Threading.Tasks.Task<bool> updateMessage(TimelineModel message)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            if (message.Title == "" || message.Message == "")
                return false;
            props.Add("title", message.Title);
            props.Add("message", message.Message);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "timeline/message/" + message.TimelineId + "/" + message.Id);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(json);
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                if (message.TimelineId == _Customer.Id)
                {
                    _CustomerMessages.Clear();
                    CustomerOffset = 0;
                    await getCustomerMessages();
                }
                else
                {
                    _TeamMessages.Clear();
                    TeamOffset = 0;
                    await getTeamMessages();
                }
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        public async System.Threading.Tasks.Task<bool> updateComment(TimelineModel message)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            if (message.Comment == "")
                return false;
            props.Add("commentId", message.Id);
            props.Add("comment", message.Comment);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "timeline/comment/" + _messageSelected.TimelineId);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(json);
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                await getComments(message.TimelineId, message.ParentId);
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(json));
                await msgbox.ShowAsync();
                return false;
            }
            props.Clear();
            return true;
        }

        #endregion API

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

        #endregion Observable Collection

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

        #endregion Select

        #region Model

        public int Id
        {
            get { return _messageSelected.Id; }
        }

        public UserModel creator
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
            get
            {
                if (_messageSelected == null)
                    return DateTime.Today;
                DateTime name;
                name = DateTime.Parse(_messageSelected.CreatedAt).ToLocalTime();
                return name;
            }
        }

        public DateTime EditionDate
        {
            get
            {
                if (_messageSelected == null)
                    return DateTime.Today;
                DateTime name;
                name = DateTime.Parse(_messageSelected.EditedAt).ToLocalTime();
                return name;
            }
        }

        #endregion Model

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

    // Observable collection representing a text message conversation
    // that can load more items incrementally.
    public class Messages : ObservableCollection<TimelineModel>, ISupportIncrementalLoading
    {
        private bool moreItems = true;
        private bool isTeam = false;

        public Messages()
        {
        }

        public Messages(bool team)
        {
            isTeam = team;
        }

        public bool HasMoreItems { get { return moreItems; } }

        public IAsyncOperation<LoadMoreItemsResult> LoadMoreItemsAsync(uint count)
        {
            return Task.Run<LoadMoreItemsResult>(async () =>
            {
                await Windows.ApplicationModel.Core.CoreApplication.MainView.CoreWindow.Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                {
                    loadMore();
                });

                return new LoadMoreItemsResult() { Count = 5 };
            }).AsAsyncOperation<LoadMoreItemsResult>();
        }

        private async void loadMore()
        {
            TimelineViewModel vm = TimelineViewModel.GetViewModel();
            if (isTeam == true)
            {
                if (HasMoreItems)
                    moreItems = await vm.getTeamMessages();
            }
            else
            {
                if (HasMoreItems)
                    moreItems = await vm.getCustomerMessages();
            }
        }
    }
}