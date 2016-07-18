using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Tasks
{
    class TaskModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("color")]
        public string Color { get; set; }

        [JsonProperty("due_date")]
        public DateModel DueDate { get; set; }

        [JsonProperty("is_milestone")]
        public bool IsMilestone { get; set; }

        [JsonProperty("is_container")]
        public bool IsContainer { get; set; }

        [JsonProperty("tasks")]
        public List<IdTitleName> Tasks { get; set; }

        [JsonProperty("started_at")]
        public DateModel StartedAt { get; set; }

        [JsonProperty("finished_at")]
        public DateModel FinishedAt { get; set; }

        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }

        [JsonProperty("advance")]
        public int Advance { get; set; }

        [JsonProperty("creator")]
        public ProjectUserModel Creator { get; set; }

        [JsonProperty("users_assigned")]
        public List<ProjectUserModel> UsersAssigned { get; set; }

        [JsonProperty("tags")]
        public List<IdNameModel> Tags { get; set; }

        [JsonProperty("dependencies")]
        public List<IdTitleName> Dependencies { get; set; }

        public TaskModel()
        {
            Title = "";
            Description = "";
            DueDate = new DateModel();
            DueDate.date = "";
            StartedAt = new DateModel();
            StartedAt.date = "";
        }
    }
}
