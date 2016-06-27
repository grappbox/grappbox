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
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("startedAt")]
        public DateModel StartedAt { get; set; }
        [JsonProperty("dueDate")]
        public DateModel DueDate { get; set; }
        [JsonProperty("finishedAt")]
        public DateModel FinishedAt { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
    }
}
