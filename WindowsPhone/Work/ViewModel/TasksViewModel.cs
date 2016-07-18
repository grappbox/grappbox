using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Model.Tasks;
using GrappBox.Ressources;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Windows.Web.Http;

namespace GrappBox.ViewModel
{
    class TasksViewModel : ViewModelBase
    {
        static private TasksViewModel instance = null;
        private TaskModel _model = new TaskModel();
        private ObservableCollection<TaskModel> _taskList;
        private TaskModel _taskSelect = null;

        static public TasksViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new TasksViewModel();
        }
        public TasksViewModel()
        {
            instance = this;
        }

        public void setModel(TaskModel md)
        {
            _model = md;
        }

        #region API
        #region GET
        public async System.Threading.Tasks.Task getTasksList()
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "tasks/getprojecttasks");
            if (res.IsSuccessStatusCode)
            {
                _taskList = api.DeserializeArrayJson<ObservableCollection<TaskModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("TaskList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion GET

        #region DELETE
        public async System.Threading.Tasks.Task deleteTask()
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token, _taskSelect.Id };
            HttpResponseMessage res = await api.Delete(token, "tasks/taskdelete");
            if (res.IsSuccessStatusCode)
            {
                _taskList.Remove(_taskSelect);
                _taskSelect = null;
                NotifyPropertyChanged("TaskList");
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion DELETE

        #region POST
        public async System.Threading.Tasks.Task addTask()
        {
            ApiCommunication api = ApiCommunication.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("projectId", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("title", _model.Title);
            props.Add("description", _model.Description);
            props.Add("due_date", _model.DueDate);
            props.Add("started_at", _model.StartedAt);
            props.Add("is_milestone", false);
            props.Add("is_container", false);
            HttpResponseMessage res = await api.Post(props, "tasks/taskcreation");
            if (res.IsSuccessStatusCode)
            {
                _model = api.DeserializeJson<TaskModel>(await res.Content.ReadAsStringAsync());
                if (_taskList != null)
                    _taskList.Insert(0, _model);
                NotifyPropertyChanged("TaskList");

                //ContentDialog cd = new ContentDialog();
                //cd.Title = "Success";
                //cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                //cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                //cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                //var t = cd.ShowAsync();
                //await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                //t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }
        #endregion POST

        #region PUT
        public async System.Threading.Tasks.Task editTask()
        {
            ApiCommunication api = ApiCommunication.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("taskId", _model.Id);
            props.Add("title", _model.Title);
            props.Add("description", _model.Description);
            props.Add("due_date", _model.DueDate);
            props.Add("started_at", _model.StartedAt);
            HttpResponseMessage res = await api.Put(props, "tasks/taskupdate");
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
        #endregion PUT
        #endregion API

        #region Observable Collection
        public ObservableCollection<TaskModel> TaskList
        {
            get { return _taskList; }
        }
        #endregion Observable Collection

        #region Select
        public TaskModel TaskSelect
        {
            get { return _taskSelect; }
            set { _taskSelect = value; }
        }
        #endregion Select

        #region Model
        public TaskModel Model
        {
            get { return _model; }
            set { _model = value; }
        }
        public int Id
        {
            get { return _model.Id; }
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

        public DateModel BeginDate
        {
            get { return _model.StartedAt; }
            set
            {
                _model.StartedAt = value;
                NotifyPropertyChanged("BeginDate");
            }
        }

        public DateModel DueDate
        {
            get { return _model.DueDate; }
            set
            {
                _model.DueDate = value;
                NotifyPropertyChanged("DueDate");
            }
        }
        #endregion
    }
}
