using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace GrappBox.Model
{
    class Event
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
        [JsonProperty("creator")]
        public Creator Creator { get; set; }
        [JsonProperty("type")]
        public EventType Type { get; set; }
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("desciption")]
        public string Description { get; set; }
        [JsonProperty("beginDate")]
        public DateTime BeginDate { get; set; }
        [JsonProperty("endDate")]
        public DateTime EndDate { get; set; }
        [JsonProperty("createdAt")]
        public DateTime CreatedAt { get; set; }
        [JsonProperty("editedAt")]
        public DateTime EditedAt { get; set; }
        [JsonProperty("deletedAt")]
        public DateTime DeletedAt { get; set; }
    }
}
