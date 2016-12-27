using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using Windows.Web.Http;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Grappbox.ViewModel;
using Grappbox.HttpRequest;
using Grappbox.Helpers;
using System.Linq;
using Windows.Networking.PushNotifications;
using Newtonsoft.Json.Linq;
using Windows.ApplicationModel.Core;
using Windows.UI.Core;

namespace Grappbox.ViewModel
{
    class BugtrackerViewModel : ViewModelBase
    {
        static private BugtrackerViewModel instance = null;
        private BugtrackerModel _model = new BugtrackerModel();
        private ObservableCollection<BugtrackerModel> _openBugs = new ObservableCollection<BugtrackerModel>();
        private ObservableCollection<BugtrackerModel> _closeBugs = new ObservableCollection<BugtrackerModel>();
        private ObservableCollection<BugtrackerModel> _yoursBugs = new ObservableCollection<BugtrackerModel>();
        private ObservableCollection<BugtrackerModel> _commentList = new ObservableCollection<BugtrackerModel>();
        private ObservableCollection<TagModel> _tagList = new ObservableCollection<TagModel>();
        private ObservableCollection<UserModel> _userList = new ObservableCollection<UserModel>();
        private List<int> _toAdd = new List<int>();
        private List<int> _toRemove = new List<int>();
        private BugtrackerModel _openSelect;
        private BugtrackerModel _closeSelect;
        private TagModel _tagSelect;

        static public BugtrackerViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new BugtrackerViewModel();
        }
        public BugtrackerViewModel()
        {
            instance = this;
        }

        #region API
        #region Get Api
        public async System.Threading.Tasks.Task getOpenTickets()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/tickets/opened");
            if (res.IsSuccessStatusCode)
            {
                _openBugs = SerializationHelper.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                _openBugs = new ObservableCollection<BugtrackerModel>(_openBugs.Reverse());
                NotifyPropertyChanged("OpenList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getClosedTickets()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/tickets/closed");
            if (res.IsSuccessStatusCode)
            {
                _closeBugs = SerializationHelper.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                _closeBugs = new ObservableCollection<BugtrackerModel>(_closeBugs.Reverse());
                NotifyPropertyChanged("CommentList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getYoursTickets()
        {
            object[] token = { SessionHelper.GetSession().ProjectId, SessionHelper.GetSession().UserId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/tickets/user");
            if (res.IsSuccessStatusCode)
            {
                _yoursBugs = SerializationHelper.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                _yoursBugs = new ObservableCollection<BugtrackerModel>(_yoursBugs.Reverse());
                NotifyPropertyChanged("YoursList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public void getTicket(BugtrackerModel md)
        {
            _model = md;
        }

        public async System.Threading.Tasks.Task getTagList()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/project/tags");
            if (res.IsSuccessStatusCode)
            {
                _tagList = SerializationHelper.DeserializeArrayJson<ObservableCollection<TagModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("TagList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getComments()
        {
            object[] token = { _model.Id };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/comments");
            if (res.IsSuccessStatusCode)
            {
                _commentList = SerializationHelper.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CommentList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task getUsers()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "project/users");
            if (res.IsSuccessStatusCode)
            {
                _userList = SerializationHelper.DeserializeArrayJson<ObservableCollection<UserModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("UserList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        public async System.Threading.Tasks.Task reopenTicket()
        {
            object[] token = { _closeSelect.Id };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "bugtracker/ticket/reopen");
            if (res.IsSuccessStatusCode)
            {
                _openBugs.Insert(0, _closeSelect);
                _closeBugs.Remove(_closeSelect);
                _closeSelect = null;
                NotifyPropertyChanged("CloseList");
                NotifyPropertyChanged("OpenList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion

        #region Put Api
        public async System.Threading.Tasks.Task editBug()
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            List<int> items = new List<int>();
            foreach (int item in _toAdd)
            {
                foreach (var user in _model.Users)
                {
                    if (item == user.Id)
                        items.Add(item);
                }
            }
            foreach (int item in items)
            {
                _toAdd.Remove(item);
            }
            items.Clear();
            foreach (var item in _toRemove)
            {
                bool isIn = false;
                foreach (var user in _model.Users)
                {
                    if (item == user.Id)
                        isIn = true;
                }
                if (isIn == false)
                    items.Add(item);
            }
            foreach (int item in items)
            {
                _toRemove.Remove(item);
            }
            items.Clear();

            props.Add("clientOrigin", false);
            props.Add("title", _model.Title);
            props.Add("description", _model.Description);
            props.Add("addUsers", _toAdd);
            props.Add("removeUsers", _toRemove);
            props.Add("addTags", new List<int>());
            props.Add("removeTags", new List<int>());
            HttpResponseMessage res = await HttpRequestManager.Put(props, "bugtracker/ticket/" + _model.Id);
            if (res.IsSuccessStatusCode)
            {
                await getOpenTickets();
                await getClosedTickets();

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task editComment(BugtrackerModel comment)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("description", comment.Description);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "bugtracker/comment/" + comment.Id);
            if (res.IsSuccessStatusCode)
            {
                BugtrackerModel _comment = SerializationHelper.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());

                int range = _commentList.IndexOf(comment);
                _commentList.Remove(comment);
                _commentList.Insert(range, _comment);
                NotifyPropertyChanged("CommentList");

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task<bool> editTag()
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("name", _tagSelect.Name);
            props.Add("color", _tagSelect.Color);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "bugtracker/tag/" + _tagSelect.Id);
            if (res.IsSuccessStatusCode)
            {
                TagModel _comment = SerializationHelper.DeserializeJson<TagModel>(await res.Content.ReadAsStringAsync());

                int range = _tagList.IndexOf(_tagSelect);
                _tagList.Remove(_tagSelect);
                _tagList.Insert(range, _comment);
                NotifyPropertyChanged("TagList");
                _tagSelect = null;
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
            return false;
        }

        public async System.Threading.Tasks.Task assignTag(TagModel tag)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("tagId", tag.Id);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "bugtracker/tag/assign/" + _model.Id);
            if (res.IsSuccessStatusCode)
            {
                _model.Tags.Add(tag);
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task setParticipants()
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            List<int> items = new List<int>();
            foreach (int item in _toAdd)
            {
                foreach (var user in _model.Users)
                {
                    if (item == user.Id)
                        items.Add(item);
                }
            }
            foreach (int item in items)
            {
                _toAdd.Remove(item);
            }
            items.Clear();
            foreach (var item in _toRemove)
            {
                bool isIn = false;
                foreach (var user in _model.Users)
                {
                    if (item == user.Id)
                        isIn = true;
                }
                if (isIn == false)
                    items.Add(item);
            }
            foreach (int item in items)
            {
                _toRemove.Remove(item);
            }
            items.Clear();
            props.Add("toAdd", _toAdd);
            props.Add("toRemove", _toRemove);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "bugtracker/users/" + _model.Id);
            if (res.IsSuccessStatusCode)
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }
        #endregion

        #region Post API
        public async System.Threading.Tasks.Task<bool> addBug()
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            List<int> items = new List<int>();
            foreach (int item in _toAdd)
            {
                foreach (var user in _userList)
                {
                    if (item == user.Id)
                        items.Add(item);
                }
            }
            foreach (int item in items)
            {
                _toAdd.Remove(item);
            }
            items.Clear();

            props.Add("projectId", SessionHelper.GetSession().ProjectId);
            props.Add("title", _model.Title);
            props.Add("description", _model.Description);
            props.Add("clientOrigin", false);
            props.Add("users", _toAdd);
            props.Add("tags", new List<int>());
            HttpResponseMessage res = await HttpRequestManager.Post(props, "bugtracker/ticket");
            if (res.IsSuccessStatusCode)
            {
                _model = SerializationHelper.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());
                if (_openBugs != null)
                    _openBugs.Insert(0, _model);
                NotifyPropertyChanged("OpenList");
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
            return false;
        }

        public async System.Threading.Tasks.Task addComment(string description)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("comment", description);
            props.Add("parentId", _model.Id);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "bugtracker/comment");
            if (res.IsSuccessStatusCode)
            {
                BugtrackerModel _comment = SerializationHelper.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());
                _commentList.Add(_comment);
                NotifyPropertyChanged("CommentList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task<bool> addTag(string name, string color)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("projectId", SessionHelper.GetSession().ProjectId);
            props.Add("name", name);
            props.Add("color", color);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "bugtracker/tag");
            if (res.IsSuccessStatusCode)
            {
                TagModel _comment = SerializationHelper.DeserializeJson<TagModel>(await res.Content.ReadAsStringAsync());
                _comment.Name = name;
                _tagList.Add(_comment);
                NotifyPropertyChanged("TagList");
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
            return false;
        }
        #endregion

        #region Delete Api
        public async System.Threading.Tasks.Task closeTicket()
        {
            object[] token = { _openSelect.Id };
            HttpResponseMessage res = await HttpRequestManager.Delete(token, "bugtracker/ticket/close");
            if (res.IsSuccessStatusCode)
            {
                _closeBugs.Insert(0, _openSelect);
                _openBugs.Remove(_openSelect);
                _openSelect = null;
                NotifyPropertyChanged("CloseList");
                NotifyPropertyChanged("OpenList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task deleteComment(BugtrackerModel comment)
        {
            object[] token = { comment.Id };
            HttpResponseMessage res = await HttpRequestManager.Delete(token, "bugtracker/comment");
            if (res.IsSuccessStatusCode)
            {
                _commentList.Remove(comment);
                NotifyPropertyChanged("CommentList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task<bool> deleteTag()
        {
            object[] token = { _tagSelect.Id };
            HttpResponseMessage res = await HttpRequestManager.Delete(token, "bugtracker/tag");
            if (res.IsSuccessStatusCode)
            {
                _tagList.Remove(_tagSelect);
                _tagSelect = null;
                NotifyPropertyChanged("TagList");
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            return false;
        }

        public async System.Threading.Tasks.Task removeAssignTag(TagModel tag)
        {
            object[] token = { _model.Id, tag.Id };
            HttpResponseMessage res = await HttpRequestManager.Delete(token, "bugtracker/tag/remove");
            if (res.IsSuccessStatusCode)
            {
                _model.Tags.Remove(tag);

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion
        #endregion API

        #region Observable Collection
        public ObservableCollection<BugtrackerModel> OpenList
        {
            get { return _openBugs; }
        }

        public ObservableCollection<BugtrackerModel> CloseList
        {
            get { return _closeBugs; }
        }

        public ObservableCollection<BugtrackerModel> YoursList
        {
            get { return _yoursBugs; }
        }

        public ObservableCollection<BugtrackerModel> CommentList
        {
            get { return _commentList; }
        }

        public ObservableCollection<TagModel> TagList
        {
            get { return _tagList; }
        }

        public ObservableCollection<UserModel> UserList
        {
            get { return _userList; }
        }
        #endregion

        #region Select
        public BugtrackerModel OpenSelect
        {
            get { return _openSelect; }
            set { _openSelect = value; }
        }

        public BugtrackerModel CloseSelect
        {
            get { return _closeSelect; }
            set { _closeSelect = value; }
        }

        public TagModel TagSelect
        {
            get { return _tagSelect; }
            set { _tagSelect = value; }
        }
        #endregion

        #region Model
        public int Id
        {
            get { return _model.Id; }
        }

        public UserModel creator
        {
            get { return _model.Creator; }
        }

        public string Title
        {
            get { if (_model == null) return ""; string name = _model.Title; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _model.Title)
                {
                    _model.Title = value;
                    NotifyPropertyChanged("Title");
                }
            }
        }

        public string Description
        {
            get { if (_model == null) return ""; string name = _model.Description; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != _model.Description)
                {
                    _model.Description = value;
                    NotifyPropertyChanged("Description");
                }
            }
        }

        public bool IdCheck
        {
            get
            {
                if (_model.Creator != null && _model.Creator.Id != SessionHelper.GetSession().UserId)
                    return false;
                return true;
            }
        }

        public int ParentId
        {
            get { return _model.ParentId; }
        }

        public DateTime CreationDate
        {
            get { if (_model == null) return DateTime.Today; DateTime name = DateTime.Parse(_model.CreatedAt).ToLocalTime(); if (name != null) { return name; } else return DateTime.Today; }
        }

        public DateTime EditionDate
        {
            get { if (_model == null) return DateTime.Today; DateTime name = DateTime.Parse(_model.EditedAt).ToLocalTime(); if (name != null) { return name; } else return DateTime.Today; }
        }

        public List<TagModel> Tags
        {
            get { return _model.Tags; }
        }

        public List<UserModel> Users
        {
            get { return _model.Users; }
        }
        #endregion

        public List<int> ToAdd
        {
            get { return _toAdd; }
            set { _toAdd = value; }
        }
        public List<int> ToRemove
        {
            get { return _toRemove; }
            set { _toRemove = value; }
        }

        public void newModel()
        {
            _model = new BugtrackerModel();
        }
        
        private void pushNotif(PushNotificationChannel sender, PushNotificationReceivedEventArgs e)
        {
            if (e.NotificationType == PushNotificationType.Raw)
            {
                string title = JObject.Parse(e.RawNotification.Content).GetValue("title").ToString();
                switch (title)
                {
                    case "new bug":
                        BugtrackerModel tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                        _openBugs.Insert(0, tmp);
                        break;
                    default:
                        break;
                }
            }
            e.Cancel = true;
        }

        public async void OnPushNotification(PushNotificationChannel sender, PushNotificationReceivedEventArgs e)
        {
            await CoreApplication.MainView.CoreWindow.Dispatcher.RunAsync(CoreDispatcherPriority.Normal,
            () =>
            {
                if (e.NotificationType == PushNotificationType.Raw)
                {
                    string title = JObject.Parse(e.RawNotification.Content).GetValue("title").ToString();
                    BugtrackerModel tmp;
                    TagModel tag;
                    switch (title)
                    {
                        case "new bug":
                             tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            _openBugs.Insert(0, tmp);
                            break;
                        case "update bug":
                        case "assign tag bug":
                        case "participants bug":
                            tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            int pos = 0;
                            foreach (var item in _openBugs)
                            {
                                if (item.Id == tmp.Id)
                                    pos = _openBugs.IndexOf(item);
                            }
                            if (pos != 0)
                                _openBugs[pos] = tmp;
                            if (tmp.Id == _openSelect.Id)
                                _openSelect = tmp;
                            if (tmp.Id == _closeSelect.Id)
                                _closeSelect = tmp;
                            break;
                        case "close bug":
                            tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            _openBugs.Remove(tmp);
                            _closeBugs.Insert(0, tmp);
                            break;
                        case "reopen bug":
                            tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            _closeBugs.Remove(tmp);
                            _openBugs.Insert(0, tmp);
                            break;
                        case "new comment bug":
                            tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            if (_commentList[0].ParentId == tmp.ParentId)
                                _commentList.Insert(0, tmp);
                            break;
                        case "edit comment bug":
                            tmp = SerializationHelper.DeserializeObject<BugtrackerModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            if (_commentList[0].ParentId == tmp.ParentId)
                            {
                                int pos2 = 0;
                                foreach (var item in _commentList)
                                {
                                    if (item.Id == tmp.Id)
                                        pos2 = _commentList.IndexOf(item);
                                }
                                _commentList[pos2] = tmp;
                            }
                            break;
                        case "new tag bug":
                            tag = SerializationHelper.DeserializeObject<TagModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            _tagList.Insert(0, tag);
                            break;
                        case "update tag bug":
                            tag = SerializationHelper.DeserializeObject<TagModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            int pos3 = 0;
                            foreach (var item in _tagList)
                            {
                                if (item.Id == tag.Id)
                                    pos3 = _tagList.IndexOf(item);
                            }
                            _tagList[pos3] = tag;
                            break;
                        case "delete tag bug":
                            tag = SerializationHelper.DeserializeObject<TagModel>(JObject.Parse(e.RawNotification.Content).GetValue("body").ToString());
                            _tagList.Remove(tag);
                            break;
                        default:
                            break;
                    }
                }
                e.Cancel = true;
            });
        }
    }
}
