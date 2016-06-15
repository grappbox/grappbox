using GrappBox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectListModel : ViewModelBase
    {
        [JsonProperty("project_id")]
        public int Id { get; set; }
        [JsonProperty("project_name")]
        public string Name { get; set; }
        [JsonProperty("project_description")]
        public string Description { get; set; }
        [JsonProperty("project_phone")]
        public string Phone { get; set; }
        [JsonProperty("project_company")]
        public string Company { get; set; }
        [JsonProperty("project_logo")]
        public string Logo { get; set; }
        [JsonProperty("contact_mail")]
        public string Email { get; set; }
        [JsonProperty("facebook")]
        public string Facebook { get; set; }
        [JsonProperty("twitter")]
        public string Twitter { get; set; }
        [JsonProperty("deleted_at")]
        public DateModel DeletedAt { get; set; }
        [JsonProperty("number_finished_tasks")]
        public int FinishedTasks { get; set; }
        [JsonProperty("number_ongoing_tasks")]
        public int OngoingTasks { get; set; }
        [JsonProperty("number_tasks")]
        public int TotalTasks { get; set; }
        [JsonProperty("number_bugs")]
        public int Bugs { get; set; }
        [JsonProperty("number_messages")]
        public string Messages { get; set; }
    }
}
