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
        private ObservableCollection<TaskModel> _taskList = new ObservableCollection<TaskModel>();
        private ObservableCollection<TagModel> _tagList = new ObservableCollection<TagModel>();
        private List<int> _toAdd = new List<int>();
        private List<int> _toRemove = new List<int>();
        public ObservableCollection<TagModel> TagList
        {
            get { return _tagList; }
            set
            {
                _tagList = value;
                NotifyPropertyChanged("Tasks");
            }
        }
        public ObservableCollection<TaskModel> TaskList
        {
            get { return _taskList; }
            set
            {
                _taskList = value;
                NotifyPropertyChanged("Tasks");
            }
        }

        public async System.Threading.Tasks.Task getTagList()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "tasks/tags/project/");
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
