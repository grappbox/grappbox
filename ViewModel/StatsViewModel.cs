using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model.Stats;
using System;
using System.Collections.ObjectModel;
using Windows.UI.Popups;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    class StatsViewModel : ViewModelBase
    {
        static private StatsViewModel instance = null;
        private StatsModel _stats = new StatsModel();
        private int _random;
        
        static public StatsViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new StatsViewModel();
        }
        public StatsViewModel()
        {
            instance = this;
            _random = new Random().Next();
        }
        
        public async System.Threading.Tasks.Task getAllStats()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, "statistics");
            if (res.IsSuccessStatusCode)
            {
                _stats = SerializationHelper.DeserializeJson<StatsModel>(await res.Content.ReadAsStringAsync());
                notifyAll();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        private void notifyAll()
        {
            NotifyPropertyChanged("UserWorkingCharge");
            NotifyPropertyChanged("ProjectLimits");
            NotifyPropertyChanged("ProjectAdvancement");
            NotifyPropertyChanged("BugEvolution");
            NotifyPropertyChanged("CustomerBugs");
            NotifyPropertyChanged("BugsTagsRepartition");
            NotifyPropertyChanged("BugAssignationTracker");
            NotifyPropertyChanged("BugsUsersRepartition");
            NotifyPropertyChanged("TasksAdvancement");
            NotifyPropertyChanged("TaskInProgress");
            NotifyPropertyChanged("TaskFinished");
            NotifyPropertyChanged("TaskLate");
            NotifyPropertyChanged("TeamMessages");
            NotifyPropertyChanged("CustomerMessages");
            NotifyPropertyChanged("CustomerAccessNumber");
        }


        public ObservableCollection<UserWorkingChargeModel> UserWorkingCharge
        {
            get { return _stats.UserWorkingCharge; }
        }

        public ObservableCollection<ProjectAdvancementModel> ProjectAdvancement
        {
            get { return _stats.ProjectAdvancement; }
        }

        public ObservableCollection<BugsEvolutionModel> BugEvolution
        {
            get { return _stats.BugsEvolution; }
        }

        public ObservableCollection<BugsTagsRepartitionModel> BugsTagsRepartition
        {
            get { return _stats.BugsTagsRepartition; }
        }

        public ObservableCollection<UsersRepartitionModel> BugsUsersRepartition
        {
            get { return _stats.BugsUsersRepartition; }
        }

        public ObservableCollection<UserTasksAdvancementModel> TasksAdvancement
        {
            get { return _stats.UserTasksAdvancement; }
        }

        public ObservableCollection<UsersRepartitionModel> TasksUsersRepartition
        {
            get { return _stats.TasksRepartition; }
        }

        public string ProjectLimits
        {
            get { if (_stats.ProjectTimeLimits != null) return string.Format("The project ends in {0} days", (DateTime.Parse(_stats.ProjectTimeLimits.ProjectEnd).ToLocalTime() - DateTime.Today.ToLocalTime()).Days); return ""; }
        }

        public string CustomerBugs
        {
            get { return string.Format("Your customer has posted {0} bugs", _stats.ClientBugTracker); }
        }

        public string BugAssignationTracker
        {
            get { return string.Format("There are {0} bugs assigned and {1} bugs non assigned", _stats.BugAssignationTracker.Assigned, _stats.BugAssignationTracker.Unassigned); }
        }

        public string TaskInProgress
        {
            get
            {
                return string.Format("{0}/{1} tasks in progress", _stats.TaskStatus.Doing, _stats.TotalTasks);
            }
        }

        public string TaskFinished
        {
            get
            {
                return string.Format("{0}/{1} finished tasks", _stats.TaskStatus.Done, _stats.TotalTasks);
            }
        }

        public string TaskLate
        {
            get
            {
                return string.Format("{0}/{1} late tasks", _stats.TaskStatus.Late, _stats.TotalTasks);
            }
        }

        public string TeamMessages
        {
            get
            {
                return string.Format("There are {0} threads on your team section", _stats.TimelinesMessageNumber.Team);
            }
        }

        public string CustomerMessages
        {
            get
            {
                return string.Format("There are {0} threads on your customer section", _stats.TimelinesMessageNumber.Customer);
            }
        }

        public string CustomerAccessNumber
        {
            get
            {
                return string.Format("You have {0} customer accesses on a maximum of {1}", _stats.CustomerAccessNumber.Actual, _stats.CustomerAccessNumber.Maximum);
            }
        }

        public Int32 MinValue
        {
            get
            {
                return 0;
            }
        }
        public Int32 MaxValue
        {
            get
            {
                return 100;
            }
        }
        public Int32 IntervalValue
        {
            get
            {
                return 10;
            }
        }
    }
}
