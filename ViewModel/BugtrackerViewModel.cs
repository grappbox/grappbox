using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Ressources;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class BugtrackerViewModel : ViewModelBase
    {
        static private BugtrackerViewModel instance = null;
        private BugtrackerModel _model = new BugtrackerModel();
        private ObservableCollection<BugtrackerModel> _openBugs;
        private ObservableCollection<BugtrackerModel> _closeBugs;
        private ObservableCollection<BugtrackerModel> _commentList = new ObservableCollection<BugtrackerModel>();
        private ObservableCollection<IdNameModel> _tagList;
        private ObservableCollection<IdNameModel> _stateList;
        private ObservableCollection<ProjectUserModel> _userList;
        private List<int> _toAdd = new List<int>();
        private List<int> _toRemove = new List<int>();
        private BugtrackerModel _openSelect;
        private BugtrackerModel _closeSelect;
        private IdNameModel _tagSelect;

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

        #region Get Api
        public async void getOpenTickets()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "bugtracker/gettickets");
            if (res.IsSuccessStatusCode)
            {
                _openBugs = api.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("OpenList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void getClosedTickets()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "bugtracker/getclosedtickets");
            if (res.IsSuccessStatusCode)
            {
                _closeBugs = api.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CommentList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public void getTicket(BugtrackerModel md)
        {
            _model = md;
        }

        public async void getStateList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "bugtracker/getstates");
            if (res.IsSuccessStatusCode)
            {
                _stateList = api.DeserializeArrayJson<ObservableCollection<IdNameModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("StateList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void getTagList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "bugtracker/getprojecttags");
            if (res.IsSuccessStatusCode)
            {
                _tagList = api.DeserializeArrayJson<ObservableCollection<IdNameModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("TagList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void getComments()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _model.Id };
            HttpResponseMessage res = await api.Get(token, "bugtracker/getcomments");
            if (res.IsSuccessStatusCode)
            {
                _commentList = api.DeserializeArrayJson<ObservableCollection<BugtrackerModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("CommentList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void getUsers()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "projects/getusertoproject");
            if (res.IsSuccessStatusCode)
            {
                _userList = api.DeserializeArrayJson<ObservableCollection<ProjectUserModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("UserList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion

        #region Put Api
        public async void reopenTicket()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            HttpResponseMessage res = await api.Put(props, "bugtracker/reopenticket/" + User.GetUser().Token + "/" + _closeSelect.Id);
            if (res.IsSuccessStatusCode)
            {
                _openBugs.Insert(0, _closeSelect);
                _closeBugs.Remove(_closeSelect);
                _closeSelect = null;
                NotifyPropertyChanged("CloseList");
                NotifyPropertyChanged("OpenList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void editBug()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("bugId", _model.Id);
            if (_model.Title != null && _model.Title != "")
                props.Add("title", _model.Title);
            if (_model.Description != null && _model.Description != "")
                props.Add("description", _model.Description);
            if (_model.State != null)
                props.Add("stateId", _model.State.Id);
            if (_model.State != null && _model.State.Name != "")
                props.Add("stateName", _model.State.Name);
            HttpResponseMessage res = await api.Put(props, "bugtracker/editticket");
            if (res.IsSuccessStatusCode)
            {
                _model = api.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());
                if (_model.Id == _openSelect.Id)
                {
                    int range = _openBugs.IndexOf(_openSelect);
                    _openBugs.Remove(_openSelect);
                    _openBugs.Insert(range, _model);
                    NotifyPropertyChanged("OpenList");
                }
                else if (_model.Id == _closeSelect.Id)
                {
                    int range = _closeBugs.IndexOf(_openSelect);
                    _closeBugs.Remove(_closeSelect);
                    _closeBugs.Insert(range, _model);
                    NotifyPropertyChanged("CloseList");
                }

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

        public async void editComment(BugtrackerModel comment)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("commentId", comment.Id);
            if (comment.Title != null && comment.Title != "")
                props.Add("title", comment.Title);
            if (comment.Description != null && comment.Description != "")
                props.Add("description", comment.Description);
            HttpResponseMessage res = await api.Put(props, "bugtracker/editcomment");
            if (res.IsSuccessStatusCode)
            {
                BugtrackerModel _comment = api.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());

                int range = _commentList.IndexOf(comment);
                _commentList.Remove(comment);
                _commentList.Insert(range, _comment);
                NotifyPropertyChanged("CommentList");

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

        public async void editTag()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("tagId", _tagSelect.Id);
            props.Add("name", _tagSelect.Name);
            HttpResponseMessage res = await api.Put(props, "bugtracker/tagupdate");
            if (res.IsSuccessStatusCode)
            {
                IdNameModel _comment = api.DeserializeJson<IdNameModel>(await res.Content.ReadAsStringAsync());

                int range = _tagList.IndexOf(_tagSelect);
                _tagList.Remove(_tagSelect);
                _tagList.Insert(range, _comment);
                NotifyPropertyChanged("TagList");
                _tagSelect = null;

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

        public async void assignTag(IdNameModel tag)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("tagId", tag.Id);
            props.Add("bugId", _model.Id);
            HttpResponseMessage res = await api.Put(props, "bugtracker/assigntag");
            if (res.IsSuccessStatusCode)
            {
                _model.Tags.Add(tag);

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

        public async void setParticipants()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
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
            props.Add("token", User.GetUser().Token);
            props.Add("bugId", _model.Id);
            props.Add("toAdd", _toAdd);
            props.Add("toRemove", _toRemove);
            HttpResponseMessage res = await api.Put(props, "bugtracker/setparticipants");
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
        #endregion

        #region Post API
        public async void addBug()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("title", _model.Title);
            props.Add("description", _model.Description);
            props.Add("stateId", _model.State.Id);
            props.Add("stateName", _model.State.Name);
            props.Add("clientOrigin", false);
            HttpResponseMessage res = await api.Post(props, "bugtracker/postticket");
            if (res.IsSuccessStatusCode)
            {
                _model = api.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());
                if (_openBugs != null)
                    _openBugs.Insert(0, _model);
                NotifyPropertyChanged("OpenList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async void addComment(string title, string description)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("title", title);
            props.Add("description", description);
            props.Add("parentId", _model.Id);
            HttpResponseMessage res = await api.Post(props, "bugtracker/postcomment");
            if (res.IsSuccessStatusCode)
            {
                BugtrackerModel _comment = api.DeserializeJson<BugtrackerModel>(await res.Content.ReadAsStringAsync());
                _commentList.Insert(0, _comment);
                NotifyPropertyChanged("CommentList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async void addTag(string name)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("name", name);
            HttpResponseMessage res = await api.Post(props, "bugtracker/tagcreation");
            if (res.IsSuccessStatusCode)
            {
                IdNameModel _comment = api.DeserializeJson<IdNameModel>(await res.Content.ReadAsStringAsync());
                _comment.Name = name;
                _tagList.Add(_comment);
                NotifyPropertyChanged("TagList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }
        #endregion

        #region Delete Api
        public async void closeTicket()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, _openSelect.Id };
            HttpResponseMessage res = await api.Delete(token, "bugtracker/closeticket");
            if (res.IsSuccessStatusCode)
            {
                _closeBugs.Insert(0, _openSelect);
                _openBugs.Remove(_openSelect);
                _openSelect = null;
                NotifyPropertyChanged("CloseList");
                NotifyPropertyChanged("OpenList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void deleteComment(BugtrackerModel comment)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, comment.Id };
            HttpResponseMessage res = await api.Delete(token, "bugtracker/closeticket");
            if (res.IsSuccessStatusCode)
            {
                _commentList.Remove(comment);
                NotifyPropertyChanged("CommentList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void deleteTag()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, _tagSelect.Id };
            HttpResponseMessage res = await api.Delete(token, "bugtracker/deletetag");
            if (res.IsSuccessStatusCode)
            {
                _tagList.Remove(_tagSelect);
                _tagSelect = null;
                NotifyPropertyChanged("TagList");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async void removeAssignTag(IdNameModel tag)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, _model.Id, tag.Id };
            HttpResponseMessage res = await api.Delete(token, "bugtracker/removetag");
            if (res.IsSuccessStatusCode)
            {
                _model.Tags.Remove(tag);

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
        #endregion

        #region Observable Collection
        public ObservableCollection<BugtrackerModel> OpenList
        {
            get { return _openBugs; }
        }

        public ObservableCollection<BugtrackerModel> CloseList
        {
            get { return _closeBugs; }
        }

        public ObservableCollection<IdNameModel> StateList
        {
            get { return _stateList; }
        }
        public ObservableCollection<BugtrackerModel> CommentList
        {
            get { return _commentList; }
        }

        public ObservableCollection<IdNameModel> TagList
        {
            get { return _tagList; }
        }

        public ObservableCollection<ProjectUserModel> UserList
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

        public IdNameModel TagSelect
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

        public Creator creator
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

        public int ParentId
        {
            get { return _model.ParentId; }
        }

        public DateTime CreationDate
        {
            get { if (_model == null) return DateTime.Today; DateTime name = DateTime.Parse(_model.CreatedAt.date); if (name != null) { return name; } else return DateTime.Today; }
        }

        public DateTime EditionDate
        {
            get { if (_model == null) return DateTime.Today; DateTime name = DateTime.Parse(_model.EditedAt.date); if (name != null) { return name; } else return DateTime.Today; }
        }

        public IdNameModel State
        {
            get { if (_model != null) return _model.State; else return null; }
            set
            {
                if (value != _model.State)
                {
                    _model.State = value;
                    NotifyPropertyChanged("State");
                }
            }
        }

        public List<IdNameModel> Tags
        {
            get { return _model.Tags; }
        }

        public List<Users> Users
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
    }
}
