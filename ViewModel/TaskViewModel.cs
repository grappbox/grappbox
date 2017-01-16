using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    class TaskViewModel : ViewModelBase
    {
        public string TaskListFilter = "All";
        private ObservableCollection<TaskModel> _taskList = new ObservableCollection<TaskModel>();
        public ObservableCollection<TaskModel> TaskList
        {
            get { return _taskList; }
            set
            {
                _taskList = value;
                NotifyPropertyChanged("TaskList");
            }
        }

        public ObservableCollection<TaskModel> FilteredTaskList
        {
            get
            {
                switch (TaskListFilter)
                {
                    case "All":
                        return TaskList;
                    case "Started":
                        return StartedTaskList;
                    case "Finished":
                        return FinishedTaskList;
                    default:
                        return TaskList;
                }
            }
        }

        public ObservableCollection<TaskModel> FinishedTaskList
        {
            get
            {
                return new ObservableCollection<TaskModel>(_taskList?.Where(t => t.FinishedAt != null).ToList());
            }
        }

        public ObservableCollection<TaskModel> StartedTaskList
        {
            get
            {
                return new ObservableCollection<TaskModel>(_taskList?.Where(t => t.StartedAt != null).ToList());
            }
        }

        public async Task GetTasks()
        {
            object[] parameters = new object[1];
            parameters[0] = SessionHelper.GetSession()?.ProjectId;
            var res = await HttpRequestManager.Get(parameters, Constants.GetProjectTasks);
            if (res.IsSuccessStatusCode == true)
            {
                string json = await res.Content.ReadAsStringAsync();
                JsonSerializerSettings settings = new JsonSerializerSettings()
                {
                    DateFormatString = "yyyy-MM-dd HH:mm:ss"
                };
                TaskList = SerializationHelper.DeserializeArrayJson<ObservableCollection<TaskModel>>(json, settings);
                foreach (var t in TaskList)
                    Debug.WriteLine("Task: " + t.Description + "  duedate: " + t.DueDate.ToString());
            }
        }
    }
}
