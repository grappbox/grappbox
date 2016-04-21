using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class TimelineModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("creator")]
        public Creator Creator { get; set; }

        [JsonProperty("timelineId")]
        public int TimelineId { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("message")]
        public string Message { get; set; }

        [JsonProperty("parentId")]
        public object parent { get { return ParentId; } set { if (value == null) ParentId = 0; else ParentId = Convert.ToInt32(value); } }
        public int ParentId { get; set; }

        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }

        [JsonProperty("editedAt")]
        public DateModel EditedAt { get; set; }
    }
}
