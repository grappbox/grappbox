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
    class GanttViewModel : ViewModelBase
    {
        public DateTime StartDate;
        public DateTime EndDate;
        public ObservableCollection<TaskModel> Dates;
        private List<TaskModel> _tasks;
        public List<TaskModel> Tasks
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
                var tasks = SerializationHelper.DeserializeArrayJson<List<TaskModel>>(json, settings);
                Tasks = tasks.OrderBy(t => t.StartedAt).ToList();
                StartDate = Tasks[0].StartedAt ?? new DateTime();
                EndDate = Tasks.Last().DueDate;
            }
        }

        public void CreateDateTable()
        {

        }
    }
}
