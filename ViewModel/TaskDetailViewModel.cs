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
    class TaskDetailViewModel : ViewModelBase
    {
        private TaskModel _model;
        public TaskModel Model
        {
            get { return _model; }
            set { _model = value; NotifyPropertyChanged("Model"); }
        }
        public string TaskListFilter = "All";
        public List<UserModel> ProjectUsers;
        public List<UserModel> FreeUsers
        {
            get
            {
                return ProjectUsers.Where(u => !AssignedUsers.Any(x => x.Id == u.Id)).ToList();
            }
        }

        public List<TaskModel> FreeDependencies
        {
            get { return TaskList.Where(t => !_dependenciesList.Any(x => x.Task.Id == t.Id)).ToList(); }
        }

        public List<TaskModel> FreeTasks
        {
            get { return TaskList.Where(t => !_assignedTaskList.Any(x => x.Id == t.Id)).ToList(); }
        }
        public ObservableCollection<TagModel> TagList;
        public ObservableCollection<TaskModel> TaskList;
        private ObservableCollection<TagModel> _assignedTagList = new ObservableCollection<TagModel>();
        private ObservableCollection<TaskUserModel> _assignedUsers = new ObservableCollection<TaskUserModel>();
        private ObservableCollection<TaskModel> _assignedTaskList = new ObservableCollection<TaskModel>();
        private ObservableCollection<DependencyTask> _dependenciesList = new ObservableCollection<DependencyTask>();
        private List<int> _toAdd = new List<int>();
        private List<int> _toRemove = new List<int>();
        public ObservableCollection<TagModel> AssignedTagList
        {
            get { return _assignedTagList; }
            set
            {
                _assignedTagList = value;
                NotifyPropertyChanged("AssignedTagList");
            }
        }
        public ObservableCollection<TaskUserModel> AssignedUsers
        {
            get { return _assignedUsers; }
            set
            {
                _assignedUsers = value;
                NotifyPropertyChanged("AssignedUsers");
            }
        }
        public ObservableCollection<TaskModel> AssignedTaskList
        {
            get { return _assignedTaskList; }
            set
            {
                _assignedTaskList = value;
                NotifyPropertyChanged("AssignedTaskList");
            }
        }
        public ObservableCollection<DependencyTask> DependenciesList
        {
            get { return _dependenciesList; }
            set
            {
                _dependenciesList = value;
                NotifyPropertyChanged("DependenciesList");
            }
        }

        public async Task getTagList()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "tasks/tags/project");
            if (res.IsSuccessStatusCode)
            {
                var json = await res.Content.ReadAsStringAsync();
                TagList = SerializationHelper.DeserializeArrayJson<ObservableCollection<TagModel>>(json);
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async Task GetUsersList()
        {
            SessionHelper session = SessionHelper.GetSession();
            object[] values = new object[1];
            values[0] = session.ProjectId;
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Get(values, Constants.GetProjectUsers);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                var list = SerializationHelper.DeserializeArrayJson<List<UserModel>>(json);
                ProjectUsers = new List<UserModel>(list);
            }
            else
            {
                ProjectUsers = new List<UserModel>();
            }
        }

        public async System.Threading.Tasks.Task<bool> addTag(TagModel model)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("projectId", SessionHelper.GetSession().ProjectId);
            props.Add("name", model.Name);
            props.Add("color", model.Color);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "tasks/tag");
            if (res.IsSuccessStatusCode)
            {
                return true;
            }
            else
            {
                var error = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                var dialog = new MessageDialog(error);
                await dialog.ShowAsync();
            }
            props.Clear();
            return false;
        }

        public async System.Threading.Tasks.Task<bool> CreateTask()
        {
            List<int> tasksAdd = new List<int>();
            List<int> dependenciesAdd = new List<int>();
            List<int> tagsAdd = new List<int>();
            foreach (var t in AssignedTaskList)
                tasksAdd.Add(t.Id);
            foreach (var d in DependenciesList)
                dependenciesAdd.Add(d.Id);
            foreach (var t in AssignedTagList)
                tagsAdd.Add(t.Id);
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("title", Model.Title);
            props.Add("description", Model.Description);
            props.Add("projectId", Model.ProjectId);
            props.Add("due_date", Model.DueDate == null ? "" : Model.DueDate?.ToString("yyyy-MM-dd HH:mm:ss"));
            props.Add("started_at", Model.StartedAt == null ? "" : Model.StartedAt?.ToString("yyyy-MM-dd HH:mm:ss"));
            props.Add("is_milestone", Model.IsMilestone);
            props.Add("is_container", Model.IsContainer);
            props.Add("dependencies", dependenciesAdd);
            if (Model.IsContainer)
            {
                props.Add("tasksAdd", tasksAdd);
            }
            if (!Model.IsContainer && !Model.IsMilestone)
            {
                props.Add("tagsAdd", tagsAdd);
                props.Add("advance", Model.Advance);
                props.Add("usersAdd", AssignedUsers);
            }

            HttpResponseMessage res = await HttpRequestManager.Post(props, "task");
            if (res.IsSuccessStatusCode)
                return true;
            props.Clear();
            return false;
        }

        public async System.Threading.Tasks.Task<bool> EditTask()
        {
            List<int> userRemove = new List<int>();
            List<int> tasksAdd = new List<int>();
            List<int> dependenciesAdd = new List<int>();
            List<int> tagsAdd = new List<int>();
            List<int> tasksRemove = new List<int>();
            List<int> dependenciesRemove = new List<int>();
            List<int> tagsRemove = new List<int>();
            var tagDiff = AssignedTagList.Where(t => !Model.Tags.Any(x => x.Id == t.Id)).ToList();
            foreach (var t in tagDiff)
                tagsAdd.Add(t.Id);
            tagDiff = Model.Tags.Where(t => !AssignedTagList.Any(x => x.Id == t.Id)).ToList();
            foreach (var t in tagDiff)
                tagsRemove.Add(t.Id);

            var taskDiff = AssignedTaskList.Where(t => !Model.Tasks.Any(x => x.Id == t.Id)).ToList();
            foreach (var t in taskDiff)
                tasksAdd.Add(t.Id);
            taskDiff = Model.Tasks.Where(t => !AssignedTaskList.Any(x => x.Id == t.Id)).ToList();
            foreach (var t in taskDiff)
                tasksRemove.Add(t.Id);

            var depDiff = DependenciesList.Where(t => !Model.Dependencies.Any(x => x.Id == t.Id)).ToList();
            foreach (var d in depDiff)
                dependenciesAdd.Add(d.Id);
            depDiff = Model.Dependencies.Where(t => !DependenciesList.Any(x => x.Id == t.Id)).ToList();
            foreach (var d in depDiff)
                dependenciesRemove.Add(d.Id);

            var userAdd = AssignedUsers.Where(t => !Model.Users.Any(x => x.Id == t.Id)).ToList();
            var userDiff = Model.Users.Where(t => !AssignedUsers.Any(x => x.Id == t.Id)).ToList();
            foreach (var u in userDiff)
                userRemove.Add(u.Id);

            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("title", Model.Title);
            props.Add("description", Model.Description);
            props.Add("projectId", Model.ProjectId);
            props.Add("due_date", Model.DueDate == null ? "" : Model.DueDate?.ToString("yyyy-MM-dd HH:mm:ss"));
            props.Add("started_at", Model.StartedAt == null ? "" : Model.StartedAt?.ToString("yyyy-MM-dd HH:mm:ss"));
            props.Add("is_milestone", Model.IsMilestone);
            props.Add("is_container", Model.IsContainer);
            props.Add("dependencies", dependenciesAdd);
            props.Add("dependenciesRemove", dependenciesRemove);
            if (Model.IsContainer)
            {
                props.Add("tasksAdd", tasksAdd);
                props.Add("tasksRemove", tasksRemove);
            }
            if (!Model.IsContainer && !Model.IsMilestone)
            {
                props.Add("tagsAdd", tagsAdd);
                props.Add("tagsRemove", tagsRemove);
                props.Add("advance", Model.Advance);
                props.Add("usersAdd", userAdd);
                props.Add("usersRemove", userRemove);
            }

            HttpResponseMessage res = await HttpRequestManager.Put(props, "task/" + Model.Id);
            if (res.IsSuccessStatusCode)
                return true;
            props.Clear();
            return false;
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
            }
        }
    }
}
