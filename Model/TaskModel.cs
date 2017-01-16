using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class TaskModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
        [JsonProperty("due_date")]
        public DateTime? DueDate { get; set; }
        [JsonProperty("started_at")]
        public DateTime? StartedAt { get; set; }
        [JsonProperty("finished_at")]
        public DateTime? FinishedAt { get; set; }
        [JsonProperty("created_at")]
        public DateTime? CreatedAt { get; set; }
        [JsonProperty("is_miletsone")]
        public bool IsMilestone { get; set; }
        [JsonProperty("is_container")]
        public bool IsContainer { get; set; }
        [JsonProperty("tasks")]
        public List<TaskModel> Tasks { get; set; }
        [JsonProperty("advance")]
        public int Advance { get; set; }
        [JsonProperty("creator")]
        public Creator Creator { get; set; }
        [JsonProperty("users")]
        public List<TaskUserModel> Users { get; set; }
        [JsonProperty("tags")]
        public ObservableCollection<TagModel> Tags { get; set; }
        [JsonProperty("dependencies")]
        public List<DependencyTask> Dependencies { get; set; }
        [JsonProperty("tasks_modified")]
        public List<TaskModel> TasksModified { get; set; }

        public string FormatDate(DateTime? date)
        {
            return date?.ToString(" dd/MM/yyyy HH:mm ");
        }

        public string FormatedStartedDate
        {
            get
            {
                return FormatDate(StartedAt) ?? " Task not started";
            }
        }
        public string FormatedFinishedDate
        {
            get
            {
                return FormatDate(FinishedAt) ?? " Task not finished";
            }
        }
        public string FormatedCreatedDate
        {
            get
            {
                return FormatDate(CreatedAt) ?? " Error";
            }
        }
        public string FormatedDueDate
        {
            get
            {
                return FormatDate(DueDate) ?? " Error";
            }
        }
    }
}
