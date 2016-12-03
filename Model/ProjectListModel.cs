using Grappbox.HttpRequest;
using Grappbox.Model;
using Grappbox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Popups;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace Grappbox.Model
{
    public class ProjectListModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("phone")]
        public string Phone { get; set; }
        [JsonProperty("company")]
        public string Company { get; set; }
        [JsonProperty("logo")]
        public string LogoDate { get; set; }
        [JsonProperty("contact_mail")]
        public string Email { get; set; }
        [JsonProperty("facebook")]
        public string Facebook { get; set; }
        [JsonProperty("twitter")]
        public string Twitter { get; set; }
        [JsonProperty("deleted_at")]
        public string DeletedAt { get; set; }
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
