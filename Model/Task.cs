using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{

    class Task
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("creatorId")]
        public int CreatorId { get; set; }
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("desciption")]
        public string Description { get; set; }
        [JsonProperty("startedAt")]
        public DateTime StartedAt { get; set; }
        [JsonProperty("dueDate")]
        public DateTime DueDate { get; set; }
        [JsonProperty("finishedAt")]
        public DateTime FinishedAt { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
    }
}
