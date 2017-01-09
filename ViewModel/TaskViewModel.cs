using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.ViewModel
{
    class TaskViewModel : ViewModelBase
    {
        private ObservableCollection<TaskModel> _tasks;
        public ObservableCollection<TaskModel> Tasks
        {
            get { return _tasks; }
            set
            {
                _tasks = value;
                NotifyPropertyChanged("Tasks");
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
                Tasks = SerializationHelper.DeserializeArrayJson<ObservableCollection<TaskModel>>(json, settings);
            }
        }
    }
}
