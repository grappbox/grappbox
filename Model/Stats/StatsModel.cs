using Newtonsoft.Json;
using System.Collections.ObjectModel;

namespace Grappbox.Model.Stats
{
    class StatsModel
    {
        #region Project
        [JsonProperty("projectTimeLimits")]
        public ProjectTimeLimitsModel ProjectTimeLimits { get; set; }

        [JsonProperty("userWorkingCharge")]
        public ObservableCollection<UserWorkingChargeModel> UserWorkingCharge { get; set; }

        [JsonProperty("projectAdvancement")]
        public ObservableCollection<ProjectAdvancementModel> ProjectAdvancement { get; set; }

        #endregion

        #region Timeline
        [JsonProperty("timelinesMessageNumber")]
        public TimelinesMessageNumberModel TimelinesMessageNumber { get; set; }
        #endregion

        #region Customer access
        [JsonProperty("customerAccessNumber")]
        public CustomerAccessNumberModel CustomerAccessNumber { get; set; }
        #endregion

        #region Bugtracker
        [JsonProperty("openCloseBug")]
        public OpenCloseBugModel OpenCloseBug { get; set; }

        [JsonProperty("clientBugTracker")]
        public int ClientBugTracker { get; set; }

        [JsonProperty("bugsEvolution")]
        public ObservableCollection<BugsEvolutionModel> BugsEvolution { get; set; }

        [JsonProperty("bugsTagsRepartition")]
        public ObservableCollection<BugsTagsRepartitionModel> BugsTagsRepartition { get; set; }

        [JsonProperty("bugAssignationTracker")]
        public BugAssignationTrackerModel BugAssignationTracker { get; set; }

        [JsonProperty("bugsUsersRepartition")]
        public ObservableCollection<UsersRepartitionModel> BugsUsersRepartition { get; set; }
        #endregion

        #region Task
        [JsonProperty("taskStatus")]
        public TaskStatusModel TaskStatus { get; set; }

        [JsonProperty("totalTasks")]
        public int TotalTasks { get; set; }

        [JsonProperty("userTasksAdvancement")]
        public ObservableCollection<UserTasksAdvancementModel> UserTasksAdvancement { get; set; }

        [JsonProperty("lateTask")]
        public ObservableCollection<LateTaskModel> LateTask { get; set; }

        [JsonProperty("tasksRepartition")]
        public ObservableCollection<UsersRepartitionModel> TasksRepartition { get; set; }
        #endregion

        #region Cloud
        /// <summary>
        /// To Ignore, just Cloud size
        /// </summary>
        [JsonProperty("storageSize")]
        public StorageSizeModel StorageSize { get; set; }
        #endregion
    }
}
